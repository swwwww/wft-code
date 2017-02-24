<?php
/**
 * cookie 帮助类
 * @Description:
 * @ClassName: CookieUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-21 上午10:31:02
 */
class CookieUtil extends Manager{

    public static function get($key){
        $cookies = Yii::app()->request->getCookies();

        $val = $cookies[$key]->value;
        $val = urldecode($val);
        return $val;
    }

    public static function set($key, $val, $time = null){
        $cookies = Yii::app()->request->getCookies();

        $time = $time == null ? 30 * 24 * 3600 : $time;

        if(YII_DEBUG == false){
            $config_arr = array(
                'path' => '/',
                'domain' => '.wanfantian.com',
            );
        }else{
            $config_arr = array(
                'path' => '/',
            );
        }
        $val = urlencode($val);
        $http_cookie = new CHttpCookie($key, $val, $config_arr);
        $http_cookie->expire = time() + $time;

        $cookies[$key] = $http_cookie;
    }

    public static function del($key){
        $cookies = Yii::app()->request->getCookies();

        $time = -10 * 24 * 2600;

        CookieUtil::set($key, '', $time);
    }
}