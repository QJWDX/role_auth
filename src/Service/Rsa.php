<?php


namespace Dx\Role\Service;

use phpseclib\Crypt\RSA as Rsa_Crypt;
class Rsa
{
    /**
     *  实例化
     * @return Rsa_Crypt
     */
    public static function instance(){
        return new Rsa_Crypt();
    }

    /** 生成rsa密钥对
     * @return array privatekey,publickey
     */
    public static function rsaCreateKey()
    {
        $rsa = static::instance();
        $keyPair = $rsa->createKey(config("rsa.length"));
        return [
            'private_key' => $keyPair['privatekey'],
            'public_key' => $keyPair['publickey'],
            'expire_time' => time() + config("rsa.ttl")
        ];
    }

    /**
     * 公钥加密
     * @param $plaintext
     * @param $public_key
     * @return string
     */
    public static function rsaEncrypt($plaintext, $public_key)
    {
        $rsa = static::instance();
        $rsa->loadKey($public_key);
        //1 - ENCRYPTION_OAEP   2-ENCRYPTION_PKCS1 3-ENCRYPTION_NONE
        $rsa->setEncryptionMode(2);
        return base64_encode($rsa->encrypt($plaintext));
    }

    /**
     * 私钥解密
     * @param $encrypted
     * @param string $private_key
     * @return string
     */
    public static function rsaDecrypt($encrypted, $private_key)
    {
        $rsa = static::instance();
        $rsa->setEncryptionMode(2);
        $rsa->loadKey($private_key);
        return $rsa->decrypt(base64_decode($encrypted));
    }


    /**
     * 签名
     * @param $plaintext
     * @param $private_key
     * @return string
     */
    public static function rsaSign($plaintext, $private_key)
    {
        $rsa = static::instance();
        $rsa->loadKey($private_key);
        $rsa->setSignatureMode(2);
        return base64_encode($rsa->sign($plaintext));

    }

    /**
     * 公钥验证
     * @param $plaintext
     * @param $signature
     * @param $public_key
     * @return bool
     */
    public static function rsaPublicVerify($plaintext, $signature, $public_key)
    {
        $rsa = static::instance();
        $rsa->setSignatureMode(2);
        $rsa->loadKey($public_key);
        return $rsa->verify($plaintext, base64_decode($signature), $signature);
    }
}
