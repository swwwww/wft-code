<?php
class CustomServiceLib extends Manager{
    //ticket 1  play 2
    public static function switchShowUrl($type, $order_sn){
        $is_admin = UserLib::checkCustomAdmin();
        DevUtil::e($is_admin); exit();
        //若是管理员，订单跳转
        if($is_admin){
            //todo...
            Yii::app()->end();
        }
    }
}