<?php


namespace Dx\Role\Http\Controllers;

use Dx\Role\Http\Requests\DeleteRequest;
use Dx\Role\Http\Requests\PermissionRequest;
use Dx\Role\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // 接口权限列表
    public function index(Request $request, Permission $permission){
        $name = $request->get('name');
        $path = $request->get('path');
        $method = strtoupper($request->get('method'));
        $data = $permission->permissionList(compact('name', 'method', 'path'));
        return $this->success($data);
    }

    // 新增接口权限
    public function store(PermissionRequest $request, Permission $permission){
        $params = $request->only(['name', 'display_name', 'path', 'method', 'status']);
        $exists = $permission->newQuery()->where(['path' => $params['path'], 'method' => $params['method']])->exists();
        if($exists){
            return $this->error('已存在同请求方式的接口');
        }
        $result = $permission->newQuery()->create($params);
        if(!$result){
            return $this->error('新增接口权限失败');
        }
        return $this->success('新增接口权限成功');
    }


    // 查询接口权限
    public function show($id){
        $role = new Permission();
        $menu = $role->getPermission(['id' => $id]);
        return $this->success($menu);
    }


    // 更新接口权限
    public function update(PermissionRequest $request, $id){
        $permission = Permission::query()->find($id);
        $params = $request->only(['name', 'display_name', 'path', 'method', 'status']);
        $permission->name = $params['name'];
        $permission->name = $params['display_name'];
        $permission->name = $params['path'];
        $permission->name = $params['method'];
        $permission->name = $params['status'];
        $result = $permission->save();
        if(!$result){
            return $this->error('编辑失败');
        }
        return $this->success('编辑权限成功');
    }

    // 删除接口权限
    public function destroy($id){
        $permission = Permission::query()->find($id);
        $result = $permission->delete();
        if($result){
            return $this->success('删除接口权限成功');
        }
        return $this->error('删除接口权限失败');
    }

    // 删除接口权限
    public function del(DeleteRequest $request, Permission $permission){
        $params = $request->get('ids');
        $permission->del($params);
        return $this->success('删除权限成功');
    }
}
