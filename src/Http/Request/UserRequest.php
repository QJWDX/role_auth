<?php
namespace Dx\Role\Http\Request;

use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    public function rules()
    {
        $id = $this->route('user');
        return [
            'name' => 'required|string|between:2,6',
            'username' => 'required|string|between:2,16',
            'phone' => 'required|regex:/^1[3456789]\d{9}$/|unique:users,phone,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'id_card' => 'string|nullable',
            'sex' => 'required|integer|'.Rule::in([0,1]),
            'avatar' => 'string|nullable',
            'birthday' => 'string|nullable',
            'address' => 'string|nullable',
            'description' => 'string|nullable',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '真实姓名',
            'username' => '用户名',
            'phone' => '手机号',
            'email' => '邮箱',
            'id_card' => '身份证',
            'sex' => '性别',
            'avatar' => '头像',
            'birthday' => '生日',
            'address' => '住址',
            'description' => '简介',
        ];
    }
}
