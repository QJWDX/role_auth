<?php


namespace Dx\Role\Http\Requests;


use Illuminate\Validation\Rule;

class PermissionRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|between:2,20',
            'display_name' => 'required|string|between:2,30',
            'path' => 'required|string|between:2,255',
            'method' => 'required|string',
            'status' => 'required|integer|'.Rule::in([0,1])
        ];
    }

    public function attributes()
    {
        return [
            'name' => '权限名称',
            'display_name' => '展示名称',
            'path' => '接口地址',
            'method' => '请求方式',
            'status' => '状态'
        ];
    }
}
