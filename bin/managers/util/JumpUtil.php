<?php
/**
 * 跳转帮助类
 * @classname: JumpUtil
 * @author 11942518@qq.com | quenteen
 * @date 2016-7-1
 */
class JumpUtil extends Manager{

    /**
     * 绝对地址 页面跳转，地址不符合，直接跳转至：http://wan.wanfantian.com
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-7-1 上午11:43:28
     */
    public static function go($url){
        if(!strstr($url, 'http')){
            $url = 'http://play.wanfantian.com';
        }
        header("Location: {$url}");
        Yii::app()->end();
    }

}