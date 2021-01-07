<?php


namespace Dx\Role\Http\Controllers;

use Dx\Role\Http\Request\RoleRequest;
use Dx\Role\Models\Menus;
use Dx\Role\Models\PermissionMenu;
use Dx\Role\Models\Role;
use Dx\Role\Models\RoleMenu;
use Dx\Role\Models\RoleUser;
use Dx\Role\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    // 角色列表
    public function index(Request $request, Role $role){
        $role_name = $request->get('role_name');
        $data = $role->roleList(compact('role_name'));
        return $this->success($data);
    }

    // 新增角色
    public function store(RoleRequest $request, Role $role){
        $params = $request->only(['role_name', 'remark']);
        $result = $role->newQuery()->create($params);
        if(!$result){
            return $this->error('新增角色失败');
        }
        return $this->success('新增角色成功');
    }


    // 查询角色
    public function show($id){
        $role = new Role();
        $result = $role->getRole(['id' => $id]);
        if(!$result){
            return $this->error('获取角色信息失败');
        }
        return $this->success($result);
    }


    // 更新角色
    public function update(RoleRequest $request, $id){
        $role = Role::query()->find($id);
        $params = $request->only(['role_name', 'remark']);
        $role->role_name = $params['role_name'];
        $role->remark = $params['remark'];
        $result = $role->save();
        if(!$result){
            return $this->error('编辑角色失败');
        }
        return $this->success('编辑角色成功');
    }


    // 删除角色
    public function destroy($id)
    {
        $role = Role::query()->find($id);
        $result = $role->delete();
        if($result){
            return $this->success('删除角色成功');
        }
        return $this->error('删除角色失败');
    }

    // 禁用启用角色
    public function changeRoleStatus(Request $request, Role $role){
        $ids = $request->get('ids', false);
        if(!$ids){
            return $this->error('请求参数有误');
        }
        $ids_arr = explode(',', $ids);
        $status = $request->get('status');
        if(empty($ids_arr)){
            return $this->error('请求参数有误');
        }
        if(!in_array($status, [0,1])){
            return $this->error('请求参数有误');
        }
        $result = $role->newQuery()->whereIn('id', $ids_arr)->update(['status' => $status]);
        $text = $status ? '启用' : '禁用';
        if(!$result){
            return $this->error($text.'失败');
        }
        return $this->success($text.'成功');
    }

    // 角色树形列表
    public function getRoleTree(Role $role){
        $list = $role->getAll([],['id', 'role_name', 'is_super']);
        $treeData = [];
        foreach ($list as $value){
            $pushData = [
                'id' => $value['id'],
                'label' => $value['role_name'] .'['. ($value->is_super ? 'super' : 'other') . ']'
            ];
            array_push($treeData, $pushData);
        }
        return $this->success($treeData);
    }

    /**
     * 角色菜单配置树形列表
     * @param $id
     * @param Menus $menus
     * @param Role $role
     * @param RoleMenu $roleMenus
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuTree($id, Menus $menus, Role $role, RoleMenu $roleMenus){
        $roleData = $role->newQuery()->find($id);
        if(!$roleData) return $this->error(500, '角色不存在');
        $menuTree = $menus->getMenuTree();
        $checkMenu = [];
        if($roleData['is_super'] == 1){
            $checkMenu = $menus->newQuery()->pluck('id');
        }else{
            $roleMenu = $roleMenus->newQuery()->where('role_id', $id)->pluck('menu_id')->toArray();
            if($roleMenu){
                $this->initCheckMenu($checkMenu, $menuTree, $roleMenu);
            }
        }
        return $this->success([
            'menu_tree' => $menuTree,
            'menu_check' => $checkMenu
        ]);
    }

    public function initCheckMenu(&$data, $trees, $menu_ids){
        foreach ($trees as $tree){
            if(count($tree['children']) == 0){
                if(in_array($tree['id'], $menu_ids)){
                    $data[] = $tree['id'];
                }
                continue;
            }
            $treeNodes = $tree['children'];
            $nodeId = [];
            foreach ($treeNodes as $node){
                if(count($node['children']) == 0){
                    continue;
                }
                $hasNodeTree[] = $node;
                $children_node_ids = collect($node['children'])->pluck('id')->toArray();
                if(count(array_diff($children_node_ids, $menu_ids))){
                    $nodeId[] = $node;
                    continue;
                }
            }
            foreach ($treeNodes as $node){
                if(count($node['children']) == 0){
                    if(in_array($node['id'], $menu_ids) && !in_array($node['id'], $nodeId)){
                        $data[] = $node['id'];
                    }
                    continue;
                }
                $this->initCheckMenu($data, $node['children'], $menu_ids);
            }
            $node_ids = collect($treeNodes)->pluck('id')->toArray();
            // 没有全部选中
            if(count(array_diff($node_ids, $menu_ids)) == 0){
                if(in_array($tree['id'], $menu_ids) && empty($nodeId)) {
                    $data[] = $tree['id'];
                }
            }
        }
    }

    /**
     * 用户管理
     * @param $id
     * @param Role $role
     * @param RoleUser $roleUser
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleUserList($id, Role $role, RoleUser $roleUser, User $user){
        if(!$role->newQuery()->where('id', $id)->exists()){
            return $this->error('角色不存在');
        }
        $check_user = $roleUser->newQuery()->where('role_id', $id)->distinct()->pluck('user_id')->toArray();
        $users = $user->newQuery()->select('id', 'name', 'username')->get()->toArray();
        $allUsers = [];
        foreach ($users as $user){
            $allUsers[] = [
                'label' => $user['username']."[".$user['name']."]",
                'key' => $user['id']
            ];
        }
        return $this->success(['all_user' => $allUsers, 'check_user' => $check_user]);
    }

    // 设置角色权限菜单
    public function setRoleMenus($id, Request $request, Role $role, RoleMenu $roleMenus, PermissionMenu $permissionMenu){
        $menus = $request->get('menus', false);
        if(!is_array($menus)){
            return $this->error(500, '参数错误');
        }
        $menus = array_unique($menus);
        $thisRole = $role->newQuery()->find($id);
        if($thisRole['is_super'] == 1){
            return $this->success('权限菜单配置成功');
        }
        $roleMenus->newQuery()->where('role_id', $id)->delete();
        $role_menu_data = [];
        foreach ($menus as $menu){
            $role_menu_data[] = ['role_id' => $id, 'menu_id' => $menu];
        }
        $result = $roleMenus->newQuery()->insert($role_menu_data);
        $permissions = $permissionMenu->newQuery()->whereIn('menu_id', $menus)->pluck('permission_id')->toArray();
        if($result){
            try {
                // 角色赋予权限
                $thisRole->perms()->sync($permissions);
                return $this->success('权限菜单配置成功');
            }catch (\Exception $exception){
                return $this->error(500, '[权限菜单配置失败]'.$exception->getMessage());
            }
        }
        return $this->error(500, '权限菜单配置失败');
    }

    /**
     * 设置角色用户
     * @param Request $request
     * @param Role $role
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function setRoleUsers(Request $request, Role $role, User $user, RoleUser $roleUser){
        $role_id = $request->get('role', false);
        $user_ids = $request->get('users', false);
        $thisRole = $role->newQuery()->find($role_id);
        if(!$thisRole){
            return $this->error(500, '角色不存在');
        }
        try {
            $roleUser->newQuery()->where('role_id', $role_id)->delete();
            $insert_data = [];
            foreach ($user_ids as $user_id){
                $insert_data[] = [
                    'role_id' => $role_id,
                    'user_id' => $user_id
                ];
            }
            $roleUser->newQuery()->insert($insert_data);
            return $this->success('角色拥有用户配置成功');
        }catch (\Exception $exception){
            return $this->error(500, '角色拥有用户配置失败');
        }
    }
}
