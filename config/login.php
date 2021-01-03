<?php

use Carbon\Carbon;

return [
    //冻结时间
    'freeze_time' => Carbon::now()->diffInSeconds(Carbon::now()->endOfDay()),
    //登陆失败次数
    'fail_times' => 5,
    //是否打开限制登录【当登录失败次数超过设置。将冻结账号】
    'fail_switch' => false,
    //失败次数时间
    'fail_times_ttl' => 30 * 60,
    //是否开启图形验证码
    'captcha' => false,
    //图形验证码的有效期
    'captcha_ttl' => 5*60,
    //是否开启短信验证码
    'sms_captcha' => true,
    //是否开启单一登录
    'open_single_sign_on' => true,
    //是否开启【验证码错误一定次数即一段时间内不允许获取验证码】
    'forbid_captcha' => true,
    //验证码错误的次数
    'fail_captcha_times' => 5,
    //验证码错误封禁获取验证码的时间
    'fail_captcha_ttl' => Carbon::now()->diffInSeconds(Carbon::now()->endOfDay())
];