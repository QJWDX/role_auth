<?php


namespace Dx\Role\Http\Controllers;


use App\Handlers\CategoryHandler;
use App\Http\Controllers\Controller;
use Dx\Role\Http\Requests\MenusRequest;
use Dx\Role\Models\Menus;
use Dx\Role\Models\Permission;
use Dx\Role\Models\PermissionMenu;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    // 菜单列表
    public function index(Menus $menus){
        $list = $menus->getList();
        return $this->success($list);
    }

    // 新增菜单
    public function store(MenusRequest $request){
        $menus = new Menus();
        $data = $request->only([
            'name',
            'parent_id',
            'icon',
            'path',
            'component',
            'is_related_route',
            'is_show',
            'is_default',
            'sort'
        ]);
        $result = $menus->newQuery()->create($data);
        if($result){
            return $this->success('新增菜单成功');
        }
        return $this->error('新增菜单失败');
    }


    // 菜单详情
    public function show($id){
        $menus = new Menus();
        $menu = $menus->newQuery()->find($id);
        return $this->success($menu);
    }


    // 更新菜单
    public function update(MenusRequest $request, $id){
        $menu = Menus::query()->find($id);
        $params = $request->only([
            'name',
            'parent_id',
            'icon',
            'path',
            'component',
            'is_related_route',
            'is_show',
            'is_default',
            'sort'
        ]);
        $menu->name = $params['name'];
        $menu->parent_id = $params['parent_id'];
        $menu->path = $params['path'];
        $menu->component = $params['component'];
        $menu->is_related_route = $params['is_related_route'];
        $menu->is_show = $params['is_show'];
        $menu->is_default = $params['is_default'];
        $menu->sort = $params['sort'];
        $result = $menu->save();
        if($result){
            return $this->success('编辑菜单成功');
        }
        return $this->error('编辑菜单失败');
    }

    // 删除菜单
    public function destroy($id){
        $menus = new Menus();
        if($menus->hasSubMenu($id)){
            return $this->success('含有子菜单,不允许删除');
        }
        $menu = $menus->newQuery()->find($id);
        $result = $menu->delete();
        if($result){
            return $this->success('删除菜单成功');
        }
        return $this->error('删除菜单失败');
    }


    /**
     * 菜单下拉框
     * @param CategoryHandler $categoryHandler
     * @param Menus $menus
     * @return \Illuminate\Http\JsonResponse
     */
    public function menuSelect(CategoryHandler $categoryHandler, Menus $menus){
        $menu = $menus->newQuery()->select(['id', 'parent_id', 'name'])->get();
        return $this->success($categoryHandler->select($menu, 0));
    }

    // 菜单权限设置穿梭框数据
    public function menuPermissionTransfer($id, Menus $menus, Permission $permission, PermissionMenu $permissionMenu){
        $exists = $menus->newQuery()->where('id', $id)->exists();
        if(!$exists){
            return $this->error('请求参数有误');
        }
        $notInPermissionId = $permissionMenu->newQuery()->pluck('permission_id')->toArray();
        $notCheckPermission = $permission->newQuery()->whereNotIn('id', $notInPermissionId)->select('id', 'name', 'path')->get()->toArray();
        $checkPermissionId = $permissionMenu->newQuery()->where('menu_id', $id)->pluck('permission_id')->toArray();
        $checkPermission = $permission->newQuery()->whereIn('id', $checkPermissionId)->select('id', 'name', 'path')->get()->toArray();
        $check = [];
        $not_check = [];
        foreach (array_merge($notCheckPermission, $checkPermission) as $v){
            $not_check[] = [
                'label' => $v['name']."[".$v['path']."]",
                'key' => $v['id']
            ];
        }
        foreach ($checkPermission as $v){
            $check[] = $v['id'];
        }
        return $this->success([
            'permission_check' => $check,
            'permission_not_check' => $not_check
        ]);
    }


    // 菜单权限设置
    public function setMenuPermission(Request $request, Menus $menus, PermissionMenu $permissionMenu){
        $id = $request->get('id');
        $permission_ids = $request->get('permission_ids');
        $exists = $menus->newQuery()->where('id', $id)->exists();
        if(!$exists){
            return $this->error('请求参数有误');
        }
        $permission_ids = empty($permission_ids) ? [] : explode(',', $permission_ids);
        $permissionMenu->newQuery()->where('menu_id',$id)->delete();
        if(count($permission_ids)){
            $insert_data = [];
            foreach ($permission_ids as $permission_id){
                $insert_data[] = [
                    'menu_id' => $id,
                    'permission_id' => $permission_id
                ];
            }
            $permissionMenu->newQuery()->insert($insert_data);
        }

        return $this->success('设置成功');
    }
}
