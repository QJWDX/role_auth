<?php


namespace Dx\Role\Http\Request;


use Dx\Role\Models\Menus;
use Illuminate\Validation\Rule;

class MenusRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|between:2,8',
            'parent_id' => 'required|integer|'.Rule::in(array_merge(Menus::query()->pluck('id')->toArray(), [0])),
            'icon' => 'required|string|between:1,255',
            'path' => 'required|string|between:1,255',
            'component' => 'required|string|between:1,255',
            'is_related_route' => 'required|integer|'.Rule::in([0,1]),
            'is_show' => 'required|integer|'.Rule::in([0,1]),
            'is_default' => 'required|integer|'.Rule::in([0,1]),
            'sort' => 'required|integer|between:0,999',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '菜单名称',
            'parent_id' => '父级菜单',
            'icon' => '菜单图标',
            'path' => '路由地址',
            'component' => '组件地址',
            'is_related_route' => '是否关联路由',
            'is_show' => '是否展示',
            'is_default' => '是否默认',
            'sort' => '排序字段',
        ];
    }
}
