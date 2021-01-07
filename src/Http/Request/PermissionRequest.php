<?php


namespace Dx\Role\Http\Request;


use Illuminate\Validation\Rule;

class PermissionRequest extends BaseRequest
{
    public function rules()
    {
        $id = $this->route('permission');
        return [
            'name' => 'required|string|between:2,100|unique:permissions,name,'.$id,
            'display_name' => 'required|string|between:2,50',
            'path' => 'required|string|between:2,255',
            'method' => 'required|string',
            'is_show' => 'required|integer|'.Rule::in([0, 1, '0', '1'])
        ];
    }

    public function attributes()
    {
        return [
            'name' => '权限名称',
            'display_name' => '接口名称',
            'path' => '接口地址',
            'method' => '请求方式',
            'is_show' => '状态'
        ];
    }
}
