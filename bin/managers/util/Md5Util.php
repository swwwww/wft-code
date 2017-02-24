<?php
/**
 * 加密计算帮助类
 * @Description:
 * @ClassName: Md5Util
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-14 下午01:33:53
 */
class Md5Util extends Manager{
    public static $salt = '#!#';

    public static function get($source){
        $target = md5($source . self::$salt);

        return $target;
    }

    public static function getBrandMd5Psw($source, $rand = 9527){
        $password = "{$source}_{$rand}";//品牌密码

        $result = Md5Util::get($password);

        return $result;
    }
}