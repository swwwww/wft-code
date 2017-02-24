<?php
/**
 * Created by PhpStorm.
 * Short Message Service
 * User: MEX
 * Date: 16/7/16
 * Time: 10:45
 */
class SmsUtil extends Manager{

    //玩翻天梦网  专门发验证码
    public static function sendMessage($phone, $content)
    {
        $content = str_replace('“玩翻天”', '', $content);  //去掉玩翻天
        $content = urlencode($content);
        $url_template = "http://120.196.116.126:8027/MWGate/wmgw.asmx/MongateCsSpSendSmsNew?userId=M10021&password=518962&pszMobis={$phone}&pszMsg={$content}&iMobiCount=1&pszSubPort=*";
        $output = file_get_contents($url_template);
        if (!$output) {
            return false;
        }
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($output);
        $status_code = $xmlDoc->getElementsByTagName('string')->item(0)->textContent;
        $strlen = strlen($status_code);
        if ($strlen >= 10 and $strlen <= 25) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param $phone
     *
     * @return bool|int
     * 发送5位数验证码,如果成功返回发送的验证码
     */
    public static function sendAuthCode($phone)
    {
        $code = self::makeCode();
        $msg = "“玩翻天”{$code}您的验证码，5分钟内有效，如非本人操作，请忽略";  //  得意生活后缀为必须添加
        $status = self::sendMessage($phone, $msg);
        return $status ? $code : false;
    }

    /**
     * @return int
     * 返回验证码 数字 五位
     */
    public static function makeCode()
    {
        return rand(10000, 99999);
    }

    public static function sendAliSms($phone){

    }
}