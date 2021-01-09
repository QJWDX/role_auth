<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::options('/{all}', function (\Illuminate\Http\Request $request) {
    return response('Ok');
})->where(['all' => '([a-zA-Z0-9-]|/)+'])->middleware("cross");

Route::group(['prefix' => 'auth/user', 'namespace' => 'Dx\Role\Http\Controllers', 'middleware' => ['bindings', 'throttle:60,1']], function () {
    // 登录
    Route::post('login', 'LoginController@login');
    // 退出登录
    Route::get('logout', 'LoginController@logout');
    // 获取图形验证码
    Route::post('captcha', 'LoginController@createCaptcha');
    // 获取rsa加密key
    Route::post('getRsaPublicKey', 'LoginController@getRsaPublicKey');
});

Route::group(['prefix' => 'api/setting', 'namespace' => 'Dx\Role\Http\Controllers','middleware' => ['api', 'role']], function (){
    // 用户管理
    Route::apiResource('user', 'UserController');
    // 获取vue动态路由和菜单
    Route::post('getUserVueRoute', 'UserController@getUserVueRoute');
    // 用户头像上传
    Route::post('userAvatarUpload', 'UserController@userAvatarUpload');
    // 检查用户密码
    Route::post('checkPassword', 'UserController@checkPassword');
    // 修改用户密码
    Route::post('userPasswordUpdate', 'UserController@userPasswordUpdate');
    // 重置用户密码
    Route::get('resetUserPassword/{id}', 'UserController@resetUserPassword');
    // 冻结启用禁用用户
    Route::get('changeUserStatus', 'UserController@changeUserStatus');
    // 菜单管理
    Route::apiResource('menus', 'MenusController');
    // 菜单列表下拉框
    Route::get('menuSelect', 'MenusController@menuSelect');
    // 菜单权限穿梭框数据
    Route::get('menuPermissionTransfer/{id}', 'MenusController@menuPermissionTransfer');
    // 设置菜单权限
    Route::post('setMenuPermission', 'MenusController@setMenuPermission');
    // 角色管理
    Route::apiResource('role', 'RoleController');
    // 刷新用户权限
    Route::get('refreshRolePermission', 'RoleController@refreshRolePermission');
    // 启用禁用角色
    Route::get('changeRoleStatus', 'RoleController@changeRoleStatus');
    // 获取角色菜单权限配置
    Route::get('getMenuTree/{id}', 'RoleController@getMenuTree');
    // 角色树形列表
    Route::get('getRoleTree', 'RoleController@getRoleTree');
    // 获取角色用户列表
    Route::get('roleUserList/{id}', 'RoleController@roleUserList');
    //设置角色菜单接口
    Route::post('setRoleMenus/{id}', 'RoleController@setRoleMenus');
    //设置角色用户接口
    Route::post('setRoleUsers', 'RoleController@setRoleUsers');
    // 权限管理
    Route::apiResource('permission', 'PermissionController');
    // 登录日志
    Route::apiResource('loginLog', 'loginLogController')->only(['index','show','destroy']);
    // 批量删除登录日志
    Route::delete('delLoginLog', 'loginLogController@delLoginLog');
});
