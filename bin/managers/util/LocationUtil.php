<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/11/14
 * Time: 10:47
 */
class LocationUtil extends Manager{

    private static $_instance;
    const REQ_GET = 1;
    const REQ_POST = 2;

    public static function instance(){
        if (!self::$_instance instanceof self){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function async($url, $params=array(), $encode = true, $method=self::REQ_GET){
        $ch = curl_init();
        if ($method == self::REQ_GET){
            $url = $url . '?' . http_build_query($params);
            $url = $encode ? $url : urlencode($url);
            curl_setopt($ch, CURLOPT_URL, $url);
        }else{
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_REFERER, '百度地图');
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X; en-us) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53'");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    private function getIP() /*获取客户端IP*/
    {
        if(getenv('HTTP_CLIENT_IP')){
            $client_ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $client_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $client_ip = getenv('REMOTE_ADDR');
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $client_ip;
    }

    public function locationByIp($ip){
        if (!filter_var($ip, FILTER_VALIDATE_IP)){
            throw new Exception('ip 地址不合法');
        }
        $params = array(
            'ak' => '5b892d47400862f9df90affd72c00228',
            'ip' => $ip,
            'coor' => 'bd0911'
        );
        $api = 'http://api.map.baidu.com/location/ip';
        $resp = $this->async($api, $params);
        $data = json_decode($resp, true);
        //有错误
        if ($data['status'] != 0)
        {
            throw new Exception($data['message']);
        }
        //返回地址信息
        return array(
            'address' => $data['content']['address'],
            'province' => $data['content']['address_detail']['province'],
            'city' => $data['content']['address_detail']['city'],
            'district' => $data['content']['address_detail']['district'],
            'street' => $data['content']['address_detail']['street'],
            'street_number' => $data['content']['address_detail']['street_number'],
            'city_code' => $data['content']['address_detail']['city_code'],
            'lng' => $data['content']['point']['x'],
            'lat' => $data['content']['point']['y']
        );
    }

    public function location(){
       return self::locationByIp(self::getIP());
    }
}