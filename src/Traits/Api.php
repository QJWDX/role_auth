<?php


namespace Dx\Role\Traits;

Trait Api
{
    public function success($data = [], $code = 200, $message = '')
    {
        if (!$data) $data = [];
        if (is_string($data)) {
            $message = $data;
            $data = [];
        }
        return response()->json([
            'code' => $code,
            'server_time' => time(),
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function error($code = 500, $message = '')
    {
        if(is_string($code)){
            $message = $code;
            $code = 500;
        }
        return response()->json([
            'code' => $code,
            'server_time' => time(),
            'message' => $message
        ]);
    }
}
