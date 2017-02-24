<?php
/**
 * @classname: SmsLib
 * @author 11942518@qq.com | quenteen
 * @date 2016-12-9
 */
class SmsLib extends Manager{
    public static function getCode($in)
    {
        $url = '/user/login/getcode';
        return HttpUtil::setP($in, $url);
    }
}