<?php


namespace Dx\Role\Http\Controllers;


use Dx\Role\Models\EmailLog;
use Dx\Role\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    //存放随机字符
    const CACHE_KEY = 'resetPWD';
    //重置密码的token
    const RESET_TOKEN = 'reset_pwd_token';

    /**
     * 检查用户邮箱
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request, User $user){
        $email = $request->get('email');
        $username = $request->get('username');
        if(!$email || !$username) return $this->error('请求参数错误');
        $account = $user->newQuery()->where('username', $username)->select('email')->first();
        if(!$account){
            return $this->error('账户不存在');
        }
        if($account['email'] !== $email){
            return $this->error('邮箱不正确');
        }
        return $this->success('邮箱正确');
    }

    /**
     * 发送邮箱验证码
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetPasswordEmail(Request $request, User $user, EmailLog $emailLog){
        try {
            $email = $request->get('email');
            $username = $request->get('username');
            if(!$email || !$username) return $this->error('请求参数错误');
            $account = $user->newQuery()->where('username', $username)->select('id','email')->first();
            if(!$account){
                return $this->error('账户不存在');
            }
            if($account['email'] !== $email){
                return $this->error('邮箱不正确');
            }
            $code = $this->createCodeStr();
            $to = $account['email'];
            Mail::raw('【后台系统】您的验证码为'.$code.',请勿告诉任何人！', function ($message)use ($to) {
                $message->to($to)->subject('找回密码');
            });
            $result = $emailLog->createLog([
                'user_id' => $account['id'],
                'email' => $account['email'],
                'code' => $code
            ]);
            if($result){
                //加key
                $key = $this->getCacheKey(self::CACHE_KEY, $account['id']);
                Cache::put($key, $code, config('uc_sms.sms_reset_effective_time', 60));
                return $this->success('发送成功');
            }
            return $this->error('发送失败');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }
    }

    public function checkEmailCode(Request $request, User $user){
        $email = $request->get('email');
        $username = $request->get('username');
        $code = $request->get('code');
        if(!$email || !$username) return $this->error('请求参数错误');
        $account = $user->newQuery()->where('username', $username)->select('id','email')->first();
        if(!$account){
            return $this->error('账户不存在');
        }
        if($account['email'] !== $email){
            return $this->error('邮箱不正确');
        }
        $key = $this->getCacheKey(self::CACHE_KEY, $account['id']);

        $str = Cache::get($key);
        if (!$str) {
            return $this->error('请先获取验证码');
        }
        if($code !== $str){
            return $this->error('验证码错误');
        }
        //删除key
        Cache::forget($key);

        //设置一个token，用于重置密码
        $token_key = $this->getCacheKey(self::RESET_TOKEN, $account['id']);
        $token = $this->createCodeStr();
        Cache::put($token_key, $token, 300);
        return $this->success(['reset_pwd_token' => $token]);
    }

    /**
     * 重置密码
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, User $user)
    {
        $username = $request->get('username', false);
        $pwd = $request->get('password', false);
        $confirm_pwd = $request->get('confirm_password', false);
        $reset_token = $request->get('reset_pwd_token', false);

        if ($username === false or $pwd === false or $confirm_pwd === false or $reset_token === false) {
           return $this->error('缺少参数');
        }

        if ($pwd !== $confirm_pwd) {
            return $this->error('两次密码不一致');
        }

        $user_info = $user->newQuery()->where('username', $username)->first();

        if (!$user_info) {
            return $this->error('用户不存在');
        }

        $token_key = $this->getCacheKey(self::RESET_TOKEN, $user_info['id']);
        $token = Cache::get($token_key);

        if ($reset_token != $token) {
            return $this->error('重置密码超时失效，请重新操作');
        }
        if (strlen($pwd) < 5 or strlen($pwd) > 12) {
            return $this->error('密码长度必须大于5位小于12位');
        }

        $password = Hash::make($pwd);

        $result = $user_info->update([
            'password' => $password
        ]);

        if (!$result) {
            return $this->error('修改失败');
        }
        //删除token参数
        Cache::forget($token_key);
        return $this->success('修改成功');
    }

    /**
     * 获取key
     * @param $key
     * @param $user_id
     * @return string
     */
    protected function getCacheKey($key, $user_id): string
    {
        return sprintf($key . "#%s", $user_id);
    }

    /**
     *  创建验证码
     * @return int
     */
    public function createCodeStr(): int
    {
        return rand(100000, 999999);
    }
}
