<?php
class WechatUtil extends Manager{

    public static function getSubscribeMsg(){
        $send_content_arr = array();


        $send_content_str = implode("\n", $send_content_arr);
        return $send_content_str;
    }

    public static function getWay($from_user_name){
        //获取注册的邀请码
        $invite_code_vo = InviteManage::getInviteCodeByWechatId($from_user_name);
        $code = $invite_code_vo['code'];

        $send_content_arr = array();


        $send_content_str = implode("\n", $send_content_arr);
        return $send_content_str;
    }

    public static function getPurpose(){
        $send_content_arr = array();


        $send_content_str = implode("\n", $send_content_arr);
        return $send_content_str;
    }

    public static function getVision(){
        $send_content_arr = array();


        $send_content_str = implode("\n", $send_content_arr);
        return $send_content_str;
    }

    public static function getEntry(){
        $send_content_arr = array();

        $send_content_str = implode("\n", $send_content_arr);
        return $send_content_str;
    }

    public static function getInviteCode($from_user_name){
        $send_content_arr = array();


        $send_content_str = implode("\n", $send_content_arr);
        return $send_content_str;
    }

}