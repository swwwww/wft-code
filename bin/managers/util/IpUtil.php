<?php
/**
 * IP定位 帮助类
 * @Description:
 * @ClassName: IpUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-21 上午10:31:02
 */
class IpUtil extends Manager{
    public static $taobao_url = 'http://ip.taobao.com/service/getIpInfo.php?ip=';

    public static function city(){
        $ip = self::getIp();
        $city = self::location($ip, 'city');

        return $city;
    }

    public static function province(){
        $ip = self::getIp();
        $province = self::location($ip);

        return $province;
    }

    public static function getIp(){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            // 反向代理情况
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["REMOTE_ADDR"])){
            $ip = $_SERVER["REMOTE_ADDR"];
        }else if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")){
            $ip = getenv("HTTP_CLIENT_IP");
        }else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")){
            echo '2';
            $ip = getenv("REMOTE_ADDR");
        }else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
            $ip = $_SERVER['REMOTE_ADDR'];
        }else{
            $ip = '';
        }

        if($ip && $ip != '::1' && $ip != '127.0.0.1'){
            $ip_array = explode(',', $ip);
            $out_ip = $ip_array[0];
        }else{
            $out_ip = null;
        }

        return $out_ip;
    }

    //定位当前城市，若定位失败，则返回默认城市 - main.php配置
    public static function location($ip, $type = 'province'){
        $instance = new IpUtilInstance();
        return $instance->location($ip, $type);
    }
}

class IpUtilInstance extends Manager{
    public function location($ip, $type){
        $default_province = YII::app()->params['province'];
        $default_city = YII::app()->params['city'];
        if(!$ip){
            $target = $type == 'province' ? $default_province : $default_city;
            return $target;
        }

        $url = IpUtil::$taobao_url . $ip;
        /*
        $proxy = new ProxyUtil('get', $url);
        $proxy_result = $proxy->run();

        $proxy_arr = json_decode($proxy_result, true);
        $code = intval($proxy_arr['code']);//0:成功 | 1:失败
        $data = $proxy_arr['data'];
        */
        $code = 1;

        if($code == 0){
            if($type == 'province'){
                $province = $data['region'];
                if($province){
                    //去除淘宝api 多余的 “市”
                    $province = str_replace('省', '', $province);
                    $province = str_replace('市', '', $province);
                }else{
                    $province = $default_province;
                }
            }else{
                $city = $data['city'];
                if($city){
                    //去除淘宝api 多余的 “市”
                    $city = str_replace('市', '', $city);
                }else{
                    $city = $default_city;
                }
            }
        }else{
            $city_arr = $this->ip->find($ip);

            if($type == 'province'){
                if($city_arr){
                    $province = $city_arr[1];
                }else{
                    $province = $default_province;
                }
            }else{
                if($city_arr){
                    $city = $city_arr[2];
                }else{
                    $city = $default_city;
                }
            }
        }

        $target = $type == 'province' ? $province : $city;

        return $target;
    }
}