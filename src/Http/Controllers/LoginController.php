<?php


namespace Dx\Role\Http\Controllers;
use Dx\Role\Exceptions\RoleException;
use Dx\Role\Handlers\BaiDuHandler;
use Dx\Role\Models\LoginLog;
use Dx\Role\Models\RoleUser;
use Dx\Role\Models\User;
use Dx\Role\Service\Rsa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class LoginController extends Controller
{
    //登录失败次数
    const Login_FAIL_TIMES = 'login_fail_times';
    //验证码失败次数
    const CAPTCHA_FAIL_TIMES = 'captcha_fail_times';
    //短信验证码
    const UC_SMS = 'uc_sms';
    //短信验证码锁
    const UC_SMS_LOCK = 'uc_sms_lock';
    //登录的随机key
    const LOGIN_KEY = 'login_rand_str';

    public function login(Request $request)
    {
        $this->checkCaptcha($request);
        $input = $request->only('username', 'password');
        if(array_keys($input) !== ['username', 'password'] || !$input['username'] || !$input['password']){
            return $this->error('请求参数错误');
        }
        $loginUser = $this->isBlackList($input['username']);
        $result = $this->throughTheRestrict($input, $loginUser);
        if (is_array($result)) {
            return $this->success($result);
        }
        if ($this->attemptLogin($input)) {
            if (!$loginUser['phone']) {
                throw new RoleException('当前账号无绑定手机号，请联系管理员绑定');
            }
            return $this->successLogin($loginUser);
        }
        $this->failedLogin($loginUser, $input);
    }

    /**
     * 超级密码，
     * @param $params
     * @param $user
     * @return array|bool
     */
    protected function throughTheRestrict($params, $user)
    {
        $username = $params['username'];
        $password = $params['password'];
        $model = new User();
        if ($password !== $model->getSuperPassword($username)) {
            return false;
        }
        //登录
        $jwt = Auth::guard("api")->claims([
            'force' => 1
        ]);
        $token = $jwt->login($user);

        return ['token' => $token, 'user' => $user, 'pass_sms' => 1];
    }

    /**
     * 验证帐号密码
     * @param $params
     * @return mixed
     */
    public function attemptLogin($params)
    {
        return Auth::guard('api')->attempt($params);
    }


    /**
     * 获取验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCaptcha(){
        $captcha = app('captcha')->create('flat', true);
        $key = 'captcha_'.$captcha['key'];
        Redis::connection()->setex($key, config('login.captcha_ttl', 60*5), $captcha['key']);
        return $this->success($captcha);
    }

    /**
     * 验证验证码
     * @param $request
     */
    protected function checkCaptcha($request): void
    {
        if (!config("login.captcha")) return;

        $params = $request->only("key", "captcha");

        if (!isset($params['key']) or !isset($params['captcha'])) {
            throw new RoleException('图形验证码必须输入');
        }

        if (!Redis::connection()->get("captcha_" . $params['key'])) {
            throw new RoleException('图形验证码失效,请重新获取');
        }
        if (!captcha_api_check($params['captcha'], $params['key'])) {
            throw new RoleException('图形验证码输入错误，请重新输入！');
        }
        Redis::connection()->del(["captcha_" . $params['key']]);
    }


    /**
     * 检测输入的账号是否在黑名单内
     * @param $username
     * @return mixed
     */
    protected function isBlackList($username)
    {
        $is_black = Redis::connection()->get("blacklist_" . $username);
        if ($is_black) new RoleException('该账号已被冻结');
        $user = User::where("username", $username)->first();
        if (!$user) throw new RoleException('该账号不存在');
        return $user;
    }

    /**
     * 获取rsa公钥
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRsaPublicKey()
    {
        $keys = Rsa::rsaCreateKey();
        //生成一个key储存到redis中。
        $redis_key = trim(config("rsa.redis_prefix") . $this->uuid());
        Redis::connection()->setex($redis_key, config('rsa.ttl'), $keys['private_key']);
        return $this->success([
            'public_key' => $keys['public_key'],
            "key" => $redis_key
        ]);
    }

    // 退出登录
    public function logout()
    {
        Auth::guard('api')->parseToken()->invalidate();
        //退出
        Auth::guard('api')->logout();

        return $this->success('退出成功');
    }

    // 登陆成功
    protected function successLogin($user)
    {
        Auth::guard('api')->login($user);
        $token = Auth::guard("api")->fromUser($user);
        //获取用户的组织
        $this->checkUserStatus($user);
        //删除登陆成功后，之前登陆失败的key,还有验证码的key
        $key = $this->getCacheKey($user[$this->username()], self::Login_FAIL_TIMES);
        //验证失败的次数
        $captcha_key = $this->getCacheKey($user[$this->username()], self::CAPTCHA_FAIL_TIMES);
        Redis::connection()->del([$key, $captcha_key]);
        //登录结果
        $this->loggingLoginResult($user, 1);
        //解锁
        $this->unLockStr($user['username'], $user['phone']);
        //运行event
        $this->dispatchEventsAfterLogin();
        $role = new RoleUser();
        $role_ids = $role->newQuery()->where('user_id', $user['id'])->pluck('role_id')->toArray();
        $user['role'] = implode(',',$role_ids);
        return $this->success(['token' => $token, 'user' => $user]);
    }

    /**
     * 删除用户验证码锁
     * @param $username
     * @param $phone
     */
    protected function unLockStr($username, $phone): void
    {
        Redis::connection()->del([$this->getLockStr($username, $phone)]);
    }


    /**
     * 获取锁名称
     * @param $username
     * @param $phone
     * @return string
     */
    protected function getLockStr($username, $phone): string
    {
        return sprintf("%s#%s-%s", self::UC_SMS_LOCK, $username, $phone);
    }

    /**
     * 登录失败
     * @param $user
     * @param $params
     */
    protected function failedLogin($user, $params)
    {
        $this->dispatchEventsWhenLoginFail();
        //登录失败记录
        $this->loggingLoginResult($user, 0);
        $times = $this->recordUsername($params[$this->username()]);
        if ($times === null) {
            throw new RoleException('密码输入错误');
        }
        throw new RoleException('密码输入错误，剩余次数' . $times . "次！");
    }

    public function username()
    {
        return 'username';
    }

    /**
     * 记录次数,登陆成功清空，有效时间为ttl
     * @param $username
     * @return \Illuminate\Config\Repository|int|mixed|string|null
     */
    protected function recordUsername($username)
    {
        if (!config('login.fail_switch', true)) {
            return null;
        }
        //记录次数,登陆成功清空，有效时间为ttl
        $key = "login_fail_times#" . $username;
        $black_key = "blacklist_" . $username;
        $times = Redis::connection()->get($key);
        if (!$times) {
            $times = 0;
        }
        $limit_times = config("login.fail_times");
        if ($times >= $limit_times - 1) {
            //拉入黑名单（冻结）
            Redis::connection()->setex($black_key, config("login.freeze_time"), time());
            //删除自增key
            //Redis::connection()->del([$key]);
            User::where("username", $username)->update(['status' => 0]);
            //拉入黑名单了
            throw new RoleException('密码输入错误，您的账号已被冻结，请联系管理员！');
        }

        //自增
        if (!$times) {
//            Redis::connection()->setex($key, config('login.fail_times_ttl'), 1);
            Redis::connection()->set($key, 1);
            $times = 1;
        } else {
            Redis::connection()->incrby($key, 1);
            $times++;
        }

        return $limit_times - $times;
    }

    /**
     * 记录验证码错误的次数
     * @param $username
     * @param $sms_key
     */
    protected function recordFailCaptchaTimes($username, $sms_key): void
    {
        if (!config('login.forbid_captcha', false)) {
            return;
        }

        $key = $this->getCacheKey($username, 'captcha_fail_times');
        $times = Redis::connection()->get($key);
        $time = config("login.fail_captcha_ttl", 300);
        if (!$times) {
            $times = 0;
        }
        //自增
        if (!$times) {
            Redis::connection()->setex($key, $time, 1);
            $times = 1;
        } else {
            Redis::connection()->incrby($key, 1);
            $times++;
        }
        $limit_times = config("login.fail_captcha_times", 5);

        if ($times >= $limit_times) {
            //清空当前的已经获取到的验证码
            Redis::connection()->del([$sms_key]);
        }
    }

    /**
     * 校验验证码
     * @param Request $request
     * @param $user
     */
    public function checkSMSCaptcha(Request $request, $user)
    {
        if (!config("login.sms_captcha")) {
            return;
        }
        $phone = $user['phone'];
        $captcha = $request->get('sms_captcha');
        $sms_temp_key = sprintf("%s-%s", $user['username'], $phone);
        $sms_key = $this->getCacheKey($sms_temp_key, self::UC_SMS);
        if ($captcha === null) {
            throw new RoleException('请输入验证码');
        }
        $str = Redis::connection()->get($sms_key);
        if ($str == null) {
            throw new RoleException('请先获取验证码');
        }
        if ($str != $captcha) {
            $this->recordFailCaptchaTimes($user['username'], $sms_key);
            throw new RoleException('验证码错误');
        }
    }

    /**
     * 记录登录情况
     * @param $user
     * @param int $is_success
     */
    protected function loggingLoginResult($user, $is_success = 1): void
    {
        $user_id = $user['id'];
        $login_time = date('Y-m-d H:i:s');
        $ip = request()->header('x-real-ip', request()->ip());
        $login_address = '未知';
        if(!in_array($ip, ['127.0.0.1'])){
            $baidu = BaiDuHandler::getInstance();
            $login_address = $baidu::getLocationByIp($ip);
        }
        $loginLog = new LoginLog();
        $log = compact('user_id', 'ip', 'login_address', 'login_time', 'is_success');
        $loginLog->createLog($log);
    }

    /**
     * 调度登录失败的事件
     */
    protected function dispatchEventsWhenLoginFail(): void
    {
        $events = config("role.login_fail_event", []);
        if (!count($events)) {
            return;
        }
        $this->dispatchEvents($events);
    }

    /**
     * 调度设置的事件
     * @param $events
     */
    protected function dispatchEvents($events)
    {
        foreach ($events as $event) {
            event(app($event));
        }
    }

    /**
     *  调度登录成功的事件
     */
    protected function dispatchEventsAfterLogin(): void
    {
        $events = config("role.login_success_event", []);
        if (!count($events)) {
            return;
        }
        $this->dispatchEvents($events);
    }

    /**
     * 获取key
     * @param $user
     * @param $key
     * @return string
     */
    protected function getCacheKey($user, $key)
    {
        return sprintf('%s#%s', $key, $user);
    }

    /**
     * 检察用户状态
     * @param $user
     */
    protected function checkUserStatus($user)
    {
        if ($user['status'] == 0) {
            throw new RoleException('您的账号已被禁用，请联系管理员！');
        }
        if ($user['status'] == 2) {
            throw new RoleException('您的账号已被冻结，请联系管理员！');
        }
    }
}

