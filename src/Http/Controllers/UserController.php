<?php

namespace Dx\Role\Http\Controllers;

use Dx\Role\Http\Request\UserRequest;
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
        $params['username'] = $request->get('username', '');
        $params['name'] = $request->get('name', '');
        $params['phone'] = $request->get('phone');
        $params['sex'] = $request->has('sex') ? intval($request->get('sex')) : -1;
        $params['status'] = $request->has('status') ? intval($request->get('status')) : -1;
        $data = $user->userList($params);
        return $this->success($data);
    }

    // 新增用户
    public function store(UserRequest $request, User $user)
    {
        $params = $request->only([
            'name',
            'username',
            'sex',
            'email',
            'phone',
            'avatar',
            'id_card',
            'birthday',
            'address',
            'description'
        ]);
        $params['password'] = Hash::make($params['username'].'@'.date('Y'));
        $res = $user->newQuery()->create($params);
        if($res){
            return $this->success('新增用户成功');
        }
        return $this->error('新增用户失败');
    }

    // 查看用户
    public function show($id)
    {
        $user = new User();
        $data = $user->newQuery()->find($id);
        return $this->success($data);
    }

    // 更新用户
    public function update(UserRequest $request, $id)
    {
        $params = $request->only([
            'name',
            'username',
            'sex',
            'email',
            'phone',
            'avatar',
            'id_card',
            'birthday',
            'address',
            'description',
        ]);
        $user = User::query()->find($id);
        $user->name = $params['name'];
        $user->username = $params['username'];
        $user->email = $params['email'];
        $user->phone = $params['phone'];
        $user->sex = $params['sex'];
        $user->id_card = $params['id_card'];
        $user->avatar = $params['avatar'];
        $user->address = $params['address'];
        $user->birthday = $params['birthday'];
        $user->description = $params['description'];
        $result = $user->save();
        if($result){
            return $this->success($user, 200, '编辑用户成功');
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
     * 判断密码是否正确
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPassword(Request $request){
        $user = Auth::guard('api')->user();
        if(!$user){
            return $this->error('请重新登录');
        }
        $params = $request->only(['password']);
        if(!isset($params['password'])){
            return $this->error('请求参数错误');
        }

        if(!Hash::check($params['password'], $user['password'])){
            return $this->error('密码输入错误');
        }
        return $this->success('密码正确');
    }
    /**
     * 修改密码
     * @param $id
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    function userPasswordUpdate(Request $request){
        $user = Auth::guard('api')->user();
        if(!$user){
            return $this->error('请重新登录');
        }
        $params = $request->only(['password', 'new_password']);
        if(!Hash::check($params['password'], $user['password'])){
            return $this->error('密码错误');
        }
        if($params['password'] == $params['new_password']){
            return $this->error('密码重复');
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
        $file = $request->file('file');
        //处理图片
        if ($file) {
            $disk_path = $file->store('', 'avatars');
            //去除根节点
            $path = str_replace(public_path(), '', config("filesystems.disks.avatars.root")) . '/' . $disk_path;
            return $this->success([
                'path' => $path,
                'full_path' => config("app.url") . $path
            ], 200, '上传成功');
        }
        return $this->error('上传失败');
    }

    /**
     * 重置密码
     * @param $id
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetUserPassword($id, User $user){
        $currentUser = $user->newQuery()->select('username')->find($id);
        if(!$currentUser){
            return $this->error('用户不存在');
        }
        $newPassword = Hash::make($currentUser['username'].'@'.date('Y'));
        Log::error($newPassword);
        $result = $user->newQuery()->where('id', $id)->update(['password' => $newPassword]);
        if($result){
            return $this->success('重置密码成功');
        }
        return $this->error('重置密码失败');
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
