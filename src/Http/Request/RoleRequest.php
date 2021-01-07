<?php


namespace Dx\Role\Http\Request;


class RoleRequest extends BaseRequest
{
    public function rules()
    {
        $id = $this->route('role');
        return [
            'name' => 'required|string|between:2,10|regex:/^[A-Za-z]*$/|unique:roles,name,'.$id,
            'display_name' => 'required|string|between:2,10',
            'remark' => 'required|string|between:0,255'
        ];
    }

    public function attributes()
    {
        return [
            'name' => '角色名称',
            'display_name' => '角色显示名称',
            'remark' => '角色备注'
        ];
    }
}
