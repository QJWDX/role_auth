<?php


namespace Dx\Role\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;

class Menus extends BaseModel
{
    protected $table = 'menus';
    protected $guarded = [];


    /**
     * 权限菜单
     * @param int $isSuper
     * @param array $menu_ids
     * @return array
     */
    public function permissionMenusAndRoute($isSuper = 0, $menu_ids = array()){
        $select = array(
            'id',
            'parent_id',
            'name',
            'icon',
            'path',
            'component'
        );
        $builder = $this->newQuery();
        $routeBuilder = clone $builder;
        if(!$isSuper){
            $builder = $builder->whereIn('id', $menu_ids);
            $routeBuilder = $routeBuilder->whereIn('id', $menu_ids);
        }
        $menus = $builder->where('is_show', 1)->latest('sort')->get($select);
        $routes = $routeBuilder->where('is_related_route', 1)->get($select);
        return [
            'menus' => $this->vueMenuTree($menus, 0, 1),
            'routes' => $routes
        ];
    }

    /**
     * 构建vue菜单
     * @param $item
     * @param $parent_id
     * @param $level
     * @return array
     */
    public function vueMenuTree(&$item, $parent_id, $level)
    {
        $list = array();
        foreach ($item as $k => $v) {
            $v['index'] = trim($v['path'], '/');
            if ($v['parent_id'] == $parent_id) {
                $v['level'] = $level;
                $v['subs'] = $this->vueMenuTree($item, $v['id'], $level + 1);
                if (empty($v['subs'])) unset($v['subs']);
                $list[] = $v;
            }
        }
        return $list;
    }


    /**
     * 获取角色菜单配置树形列表
     * @return array
     */
    public function getMenuTree(){
        $data = $this->newQuery()->where('is_show', 1)->orderBy('sort')->get()->toArray();
        return $this->dataTree($data, 0);
    }

    /**
     * el-tree树形控件
     * @param $items
     * @param $parent_id
     * @return array
     */
    public function dataTree(&$items, $parent_id){
        $list = array();
        if(!empty($items)){
            foreach ($items as $index => $item) {
                if ($item['parent_id'] == $parent_id) {
                    $list_item['id'] = $item['id'];
                    $list_item['label'] = $item['name'];
                    $children = $this->dataTree($items, $item['id']);
                    $list_item['children'] = $children;
                    array_push($list, $list_item);
                }
            }
        }
        return $list;
    }


    /**
     * 菜单列表
     * @return array
     */
    public function getList(){
        $builder = $this->builderQuery()->orderByDesc('parent_id')->orderByDesc('sort');
        return $this->paginateForApi($builder);
    }


    public function builderQuery(){
        $name = request('name', false);
        $builder = $this->newQuery();
        $builder = $builder->when($name, function ($query) use($name){
            $query->where('name', 'like', '%'. $name. '%');
        });
        return $builder;
    }


    /**
     * 是否含有子菜单
     * @param $id
     * @return bool
     */
    public function hasSubMenu($id){
        return $this->newQuery()->where('parent_id', $id)->exists();
    }


    /**
     * 获取子菜单
     * @return HasMany
     */
    public function childMenus()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
