<?php

namespace Dx\Role\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 重写父类处理验证失败方法
     * @param Validator $validator
     */
    public function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(response()->json([
            'code' => 500,
            'message' => $validator->errors()->first(),
            'server_time' => time(),
        ], 200)));
    }

    public function messages()
    {
        return [
            'string' => ':attribute类型错误，请输入字符串类型',
            'numeric' => ':attribute类型错误，请输入整数类型',
            'mix' => ':attribute范围出错',
            'uuid' => ':attribute类型错误，请输入相关编号',
            'accepted' => ':attribute必须为yes,on,1,true',
            'active_url' => ':attribute必须为合法的url，基于PHP的checkdnsrr函数验证',
            'after:date' => ':attribute验证字段必须是给定日期后的值，比如required|date|after:tomorrow,通过PHP函数strtotime来验证',
            'after_or_equal' => ':attribute大于等于',
            'alpha' => ':attribute必须全是字母',
            'alpha_dash' => ':attribute必须具有字母、数字、破折号、下划线',
            'alpha_num' => ':attribute必须全是字母和数字',
            'array' => ':attribute必须为数组',
            'before' => ':attribute必须在指定日期之前', // 这个日期将会使用 PHP strtotime 函数验证
            'before_or_equal' => ':attribute小于等于',
            'between' => ':attribute 值必须在给定值min,max范围内', // 字符串，数字，数组或者文件大小都用size函数评估
            'boolean' => ':attribute必须为能转化为布尔值的参数，比如：true,false,1,0,"1","0"',
            'confirmed' => ':attribute字段必须与需要重复的字段值一致', // 比如要验证的是password,输入中必须存在匹配的password_confirmation字段
            'date' => ':attribute不是有效日期', // 通过strtotime校验的有效日期
            'date_equals' => ':attribute不等于指定日期值',
            'date_format' => ':attribute不是指定时间格式', // date和date_format不应该同时使用，按指定时间格式传值
            'different' => ':attribute必须与字段field的值相同',
            'digits' => ':attribute必须为有确切的值数字',
            'digits_between' => ':attribute字段长度必须在min,max之间',
            'dimensions' => ':attribute文件必须是图片并且图片比例必须符合规则', // 比如dimensions:min_width=100,min_height=200,可用的规则有min_width,max_width,min_height,max_height,width,height,ratio
            'distinct' => ':attribute数组不能有重复值', // 'foo.*.id' => 'distinct'
            'email' => ':attribute格式错误，请输入正确格式',
            'exists' => ':attribute必须存在于指定的数据库表中',
            'file' => ':attribute必须是成功上传的文件',
            'filled' => ':attribute字段存在时不能为空',
            'image' => ':attribute文件必须是图像，jpeg,png,bmp,gif,svg',
            'in' => ':attribute必须包含在指定值列表中',
            'in_array' => ':attribute必须存在于另一个字段的值中',
            'integer' => ':attribute必须为整数',
            'ip' => ':attribute必须为ip地址',
            'ipv4' => ':attribute必须为ipv4地址',
            'ipv6' => ':attribute必须为ipv6地址',
            'json' => ':attribute必须为json字符串',
            'min' => ':attribute必须小于给定值',
            'max' => ':attribute必须大于给定值',
            'mimetypes' => ':attribute验证的文件必须与给定的MIME类型匹配',
            'mimes' => ':attribute验证的文件必须具有列出的其中一个扩展名对应的MIME类型',
            'nullable' => ':attribute可为null,可以包含空值的字符串和整数',
            'not_in' => ':attribute不能包含在指定值列表中',
            'present' => ':attribute验证的字段必须存在于输入数据中，但可以为空',
            'regex' => ':attribute格式有误',
            'required' => ':attribute为必填项且不能为空',
            'required_if' => ':attribute字段必须存在且不为空',
            'required_unless' => ':attribute字段不必存在',
            'required_with' => ':attribute字段必须存在且不为空',
            'required_with_all' => ':attribute字段必须存在且不为空',
            'required_without_all' => ':attribute字段必须存在且不为空',
            'required_without' => ':attribute字段必须存在且不为空',
            'same' => ':attribute给定字段必须与验证字段匹配',
            'size' => ':attribute与给定值大小不匹配',
            'timezone' => ':attribute验证字段是有效的时区标识符',
            'unique' => ':attribute已存在,请重新输入',
            'url' => ':attribute必须为有效的url',
        ];
    }
}
