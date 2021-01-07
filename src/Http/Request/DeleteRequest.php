<?php


namespace Dx\Role\Http\Request;

class DeleteRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'ids' => '待删除的主键列表'
        ];
    }
}
