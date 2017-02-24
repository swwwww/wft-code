<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 5:54
 */
class OrderWapLib extends Manager{

    // get pay ticket order sid
    public static function getPayTicketOrderSn($in){
        $url = '/pay/index/index';
        return HttpUtil::setP($in, $url);
    }

    // get pay activity order sid
    public static function getPayActivityOrderSn($in){
        $url = '/pay/excercise/index';
        return HttpUtil::setP($in, $url);
    }

    // get pay method and coupons
    public static function checkStand($in){
        $url = '/pay/index/checkstand';
        return HttpUtil::setP($in, $url);
    }

    public static function noPayInfo($in){
        $url = '/pay/index/noPayInfo';
        return HttpUtil::setP($in, $url);
    }

    //pay check_out
    public static function getOrderPayParam($in){
        $url = '/pay/index/alipay';
        return HttpUtil::setP($in, $url);
    }

    // 取消待支付订单
    public static function orderClean($in){
        $url = '/pay/index/cleanorder';
        return HttpUtil::setP($in, $url);
    }

    //遛娃活动订单详情接口 order_detail
    public static function orderPlayDetail($in){
        $url = '/kidsplay/apply/order';
        return HttpUtil::setP($in, $url);
    }

    //遛娃活动下单成功buy_completed
    public function orderPlayCompleted($in){
        $url = '/cashcoupon/index/afterplay';
        return HttpUtil::setP($in, $url);
    }

    public static function queryMyCashCoupon($in){
        $url = '/cashcoupon/index/my';
        return HttpUtil::setP($in, $url);
    }

    //商品下单接口
    public function ticketOrder($in){
        $url = '/pay/index/index';
        return HttpUtil::setP($in, $url);
    }

    // 使用余额重新支付
    public static function updateOrder($in){
        $url = '/pay/index/updateOrder';
        return HttpUtil::setP($in, $url);
    }

    // 整单退款
    public static function backPay($in){
        $url = '/pay/index/backPay';
        return HttpUtil::setP($in, $url);
    }

    //商品订单详情
    public static function nhave($in){
        $url = '/good/index/nhave';
        return HttpUtil::setP($in,$url);
    }

    //补充出行人
    public static function addAssociates($in){
        $url = '/pay/excercise/addAssociates';
        return HttpUtil::setP($in, $url);
    }

}