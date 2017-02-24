<?php
/**
 * 验证码
 * @Description:
 * @ClassName: VcodeUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-21 上午10:31:02
 */
class VcodeUtil extends Manager{

    public static function check($source){
        $source = strtolower($source);
        $right_verify_code = $_SESSION['verify_code'];

        if($source === $right_verify_code){
            return true;
        }else{
            return false;
        }
    }

    //获取验证码
    public static function get($num = 4){
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
        $code = '';
        for ($i = 0; $i < $num; $i++) {
            $code .= $str[mt_rand(0, strlen($str)-1)];
        }

        return $code;
    }

    //绘制验证码
    public static function draw($code, $num = 4, $size = 14, $width = 0, $height = 0){
        !$width && $width = $num * ($size + 2) * 4 / 5 + 5;
        !$height && $height = ($size + 2) + 10;

        // 画图像
        $im = imagecreatetruecolor($width, $height);
        // 定义要用到的颜色
        //$back_color = imagecolorallocate($im, 235, 236, 237);
        $back_color = imagecolorallocate($im, 255, 255, 255);
        $boer_color = imagecolorallocate($im, 118, 151, 199);
        $text_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        // 画背景
        imagefilledrectangle($im, 0, 0, $width, $height, $back_color);
        // 画边框
        //imagerectangle($im, 0, 0, $width-1, $height-1, $boer_color);
        // 画干扰线
        for($i = 0;$i < 5;$i++) {
            $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagearc($im, mt_rand(- $width, $width), mt_rand(- $height, $height), mt_rand(30, $width * 2), mt_rand(20, $height * 2), mt_rand(0, 360), mt_rand(0, 360), $font_color);
        }
        // 画干扰点
        for($i = 0;$i < 50;$i++) {
            $font_color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $font_color);
        }

        // 画验证码
        $font_db = DIR_DATA . 'vcode/Arial.ttf';
        @imagefttext($im, $size , 3, 3, $size + 6, $text_color, $font_db, $code);

        header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
        header("Content-type: image/png;charset=utf8");
        imagepng($im);
        imagedestroy($im);
    }

}