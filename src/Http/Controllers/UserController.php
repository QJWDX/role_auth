<?php

namespace Dx\Role\Http\Controllers;

use Dx\Role\Http\Requests\UserRequest;
use Dx\Role\Models\Menus;
use Dx\Role\Models\Role;
use Dx\Role\Models\RoleMenu;
use Dx\Role\Models\RoleUser;
use Dx\Role\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    public function index(Request $request, User $user)
    {
        $params = $request->only(['username']);
        $data = $user->userList($params);
        return $this->success($data);
    }

    // 新增用户
    public function store(UserRequest $request, User $user)
    {
        $data = $request->only(['name', 'username', 'email', 'phone', 'sex']);
        $data['password'] = Hash::make('123456');
        $res = $user->newQuery()->create($data);
        if($res){
            return $this->success('新增用户成功');
        }
        return $this->error('新增用户失败');
    }

    // 查看用户
    public function show($id)
    {
        $user = new User();
        $data = $user->newQuery()->select(['id', 'name', 'username', 'email', 'phone', 'sex', 'status'])->find($id);
        return $this->success($data);
    }

    // 更新用户
    public function update(UserRequest $request, $id)
    {
        $params = $request->only(['name', 'username', 'email', 'phone', 'sex', 'id_card', 'address']);
        $user = User::query()->find($id);
        $user->name = $params['name'];
        $user->username = $params['username'];
        $user->email = $params['email'];
        $user->phone = $params['phone'];
        $user->sex = $params['sex'];
        $user->id_card = $params['id_card'];
        $user->address = $params['address'];
        $result = $user->save();
        if($result){
            return $this->success('编辑用户成功');
        }
        return $this->error('编辑用户失败');
    }


    // 删除用户
    public function destroy($id)
    {
        $user = User::query()->find($id);
        $result = $user->delete();
        if($result){
            return $this->success('删除用户成功');
        }
        return $this->error('删除用户失败');
    }

    /**
     * 获取用户vue路由和菜单权限
     * @param Menus $menus
     * @param Role $role
     * @param RoleUser $roleUser
     * @param RoleMenu $roleMenus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserVueRoute(Menus $menus, Role $role, RoleUser $roleUser, RoleMenu $roleMenus){
        $user = Auth::guard('api')->user();
        $role_ids = $roleUser->newQuery()->where('user_id', $user['id'])->pluck('role_id')->toArray();
        $menu_ids = [];
        $isSuperRole = $role->isSuperRole($role_ids);
        if(!$isSuperRole){
            $menu_ids = $roleMenus->newQuery()->whereIn('role_id', $role_ids)->distinct()->pluck('menu_id')->toArray();
        }
        $permissionData = $menus->permissionMenusAndRoute($isSuperRole, $menu_ids);
        return $this->success($permissionData);
    }

    /**
     * 修改密码
     * @param $id
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    function userPasswordUpdate($id, Request $request){
        $user = User::query()->find($id);
        if(!$user){
            return $this->error(500, '用户不存在');
        }
        $params = $request->only(['password', 'new_password', 'confirm_password']);
        if($params['new_password'] != $params['confirm_password']){
            return $this->error('新密码与确认密码不一致');
        }
        if(!Hash::check($params['password'], $user['password'])){
            return $this->error('原密码输入错误');
        }
        if($params['password'] == $params['new_password']){
            return $this->error('与原密码重复');
        }
        $user->password = Hash::make($params['new_password']);
        $result = $user->save();
        if($result){
            return $this->success('密码修改成功');
        }
        return $this->error('密码修改失败');
    }

    // 头像上传
    public function userAvatarUpload(Request $request){
        Log::channel('test_log')->info(public_path());
        $file = $request->file('file');
        //处理图片
        if ($file) {
            $disk_url = $file->store('', 'avatars');
            //去除根节点
            $real_url = str_replace(public_path(), '', config("filesystems.disks.avatars.root")) . '/' . $disk_url;
            return $this->success([
                'src_url' => $real_url,
                'avatar_url' => env("app.url") . $real_url
            ], 200, '上传成功');
        }
        return $this->error('上传失败');
    }

    /**
     * 禁用启用冻结用户
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUserStatus(Request $request, User $user){
        $ids = $request->get('ids', false);
        if(!$ids){
            return $this->error('请求参数有误');
        }
        $ids_arr = explode(',', $ids);
        $status = $request->get('status');
        if(empty($ids_arr)){
            return $this->error('请求参数有误');
        }
        if(!in_array($status, [0,1,2])){
            return $this->error('请求参数有误');
        }
        $usernames = $user->newQuery()->whereIn('id', $ids_arr)->pluck('username')->toArray();
        $blacklist = [];
        $login_fail_times = [];
        $captcha_fail_times = [];
        foreach ($usernames as $username){
            $blacklist[] = 'blacklist_'.$username;
            $login_fail_times[] = $this->getCacheKey($username, 'login_fail_times');
            $captcha_fail_times[] =  $this->getCacheKey($username, 'captcha_fail_times');
        }
        $result = $user->newQuery()->whereIn('id', $ids_arr)->update(['status' => $status]);
        $text = '';
        switch (intval($status)){
            case 0:
                $text = '禁用';
                break;
            case 1:
                Redis::connection()->del(array_merge($blacklist, $login_fail_times, $captcha_fail_times));
                $text = '启用';
                break;
            case 2:
                $text = '冻结';
                foreach ($blacklist as $black_key){
                    Redis::connection()->setex($black_key, config("login.freeze_time"), time());
                }
                break;
        }
        if(!$result){
            return $this->error($text .'失败');
        }
        return $this->success($text .'成功');
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
}
