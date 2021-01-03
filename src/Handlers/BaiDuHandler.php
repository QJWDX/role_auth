<?php


namespace Dx\Role\Handlers;
use GuzzleHttp\Client;

class BaiDuHandler
{

    private static $instance = null;

    private function __construct(){}

    private function __clone(){}

    protected static $ak;

    const BASE_URL = 'http://api.map.baidu.com/';

    public static function getInstance()
    {
        self::$ak = config('bd.api.ak');
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public static function getLocationByIp($ip = null){
        try {
            $address = '未知';
            if($ip !== null){
                $params = [
                    'json' => [],
                    'query' => [
                        'ip' => $ip,
                        'ak' => static::$ak
                    ],
                    'http_errors' => false
                ];
                $url = self::BASE_URL . 'location/ip';
                $client = new Client();
                $response = $client->request('get', $url, $params);
                $code = $response->getStatusCode();
                $source_data = $response->getBody()->getContents();
                if($code == 200){
                    $data = json_decode($source_data, true);
                    $address = $data['content']['address'];
                }
            }
            return $address;
        }catch (\Exception $exception){
            return '';
        }
    }
}
