<?php
/**
 * 加密解密公用方法
 * @classname: SecretUtil
 * @author 11942518@qq.com | quenteen
 * @date 2016-6-28
 */
class SecretUtil extends Manager{

    public static $hex_iv = '00000000000000000000000000000000'; # converted JAVA byte code in to HEX and placed it here
    public static $key;

    public static function setKey(){
        self::$key = hash('sha256', Yii::app()->params['api_key'], true);
    }

    public static function encrypt($str){
        self::setKey();

        $td = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        @mcrypt_generic_init($td, self::$key, self::hexToStr(self::$hex_iv));
        $block = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        $encrypted = @mcrypt_generic($td, $str);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);

        return base64_encode($encrypted);
    }

    public static function decrypt($code){
        $code = preg_replace(array('/-/', '/_/'), array('+', '/'), $code);
        self::setKey();

        $td = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        @mcrypt_generic_init($td, self::$key, self::hexToStr(self::$hex_iv));
        $str = @mdecrypt_generic($td, base64_decode($code));
        $block = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);

        return self::strippadding($str);
    }

    /*
     For PKCS7 padding
     */
    public static  function addpadding($string, $blocksize = 16){
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    public static  function strippadding($string){
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);
            return $string;
        } else {
            return false;
        }
    }

    public static function hexToStr($hex){
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }
}