<?php


namespace Dx\Role\Http\Middleware;
use Dx\Role\Exceptions\DecryptException;
use Dx\Role\Service\RedisRsa;
use Dx\Role\Service\Rsa;
use Illuminate\Http\Request;

class RsaBeforeMiddleware
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($request->method() == 'OPTIONS') {
            return $next($request);
        }
        // 判断header中是否含有encryptKey
        if ($request->hasHeader("encryptKey")) {
            $encrypt_key = trim($request->header("encryptKey"));
            $key = RedisRsa::getFlashRsaKey($encrypt_key);
            if (!$key) throw new DecryptException('encrypt_key错误', 500);
            //解密Post请求中的数据
            $encrypt_data = $request->input("encrypt_data");
            if (!$encrypt_data) throw new  DecryptException('密文不存在', 500);
            $dataJson = Rsa::rsaDecrypt($encrypt_data, $key);
            if (!$dataJson) throw new  DecryptException('密文错误', 500);
            $data = json_decode($dataJson, true);
            foreach ($data as $key => $val) {
                $request->request->set($key, $val);
                $request->merge([$key => $val]);
            }
        }
        return $next($request);
    }
}
