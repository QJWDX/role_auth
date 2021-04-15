<?php


namespace Dx\Role\Service;
use Dx\Role\Exceptions\DecryptException;
use Illuminate\Support\Facades\Redis;

class RedisRsa
{
    public static function getFlashRsaKey($key)
    {
        $private_key = Redis::connection()->get($key);
        if (!$private_key) {
            throw new DecryptException(500, 'encrypt_key不存在');
        }
        Redis::connection()->del($key);
        return $private_key;
    }
}
