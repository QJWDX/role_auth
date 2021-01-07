<?php


namespace Dx\Role\Http\Request;


class RoleRequest extends BaseReques
{
    public function rules()
    {
        return [
            'role_name' => 'required|string|between:2,10',
            'remark' => 'required|string|between:0,255',
//            'is_super' => 'required|integer'
        ];
    }

    public function attributes()
    {
        return [
            'role_name' => '角色名称',
            'remark' => '角色备注',
//            'is_super' => '是否超级角色'
        ];
    }
}
