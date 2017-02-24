<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/9
 * Time: 17:03
 */
class PlayLib extends Manager
{

    //活动列表数据接口  //行程安排及往期回顾
    public function playList($in)
    {
        $url = '/kidsplay/index/list';
        return HttpUtil::setP($in, $url);
    }
    
    //活动详情数据接口
      public function playInfo($in){
        $url = '/kidsplay/index/detail';
        return HttpUtil::setP($in, $url);
    }

    //活动咨询
    public function playConsult($in){
        $url = '/kidsplay/consult';
        return HttpUtil::setP($in, $url);
    }
    //遛娃活动咨询列表
    public function playConsultLists($in){
        $url = '/kidsplay/consult/list';
        return HttpUtil::setP($in, $url);
    }

    //活动订单信息及人数选择
    public function playSeleApplic($in){
        $url = '/kidsplay/index/info';
        return HttpUtil::setP($in, $url);
    }

    //活动场次选择
    public function playChoiceField($in){
        $url = '/kidsplay/index/session';
        return HttpUtil::setP($in, $url);
    }
    //添加出行人
    public function playAddTraveller($in){
        $url = '/user/associates/add';
        return HttpUtil::setP($in, $url);
    }
    //编辑出行人
    public function playEditTraveller($in){
        $url = '/user/associates/edit';
        return HttpUtil::setP($in, $url);
    }
    //获取出行人列表
    public static function playGetTravellers(){
        $url = '/user/associates/list';
        return HttpUtil::setP(null, $url);
    }
    //删除出行人
    public function playDelTraveller($in){
        $url = '/user/associates/delete';
        return HttpUtil::setP($in, $url);
    }

    public static function backPay($in){
        $url = '/pay/excercise/backpay';
        return HttpUtil::setP($in, $url);
    }

    //我想去信息
    public static function wantGoList($in = null){
        $url = '/kidsplay/index/wantGoList';
        return HttpUtil::setP($in, $url);
    }
    public static function wantGoSubmit($in = null){
        $url = '/kidsplay/index/wantGoSubmit';
        return HttpUtil::setP($in, $url);
    }

    //定制活动信息详情
    public static function privateParty($in = null){
        $url = '/kidsplay/index/customKidsplayBaseInfo';
        return HttpUtil::setP(null, $url);
    }

    //定制活动接口
    public static function makeParty($in = null){
        $url = '/kidsplay/index/customKidsplayInfSubmit';
        return HttpUtil::setP($in,$url);
    }
}