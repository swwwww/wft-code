<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/10/10 0010
 * Time: 下午 3:44
 */
class CouponLib extends Manager{
    //现金券列表 & 活动支付选择现金券
    public function getCashCouponLists($in){
        $url = '/cashcoupon/index/nmy';
        return HttpUtil::setP($in, $url);
    }
    //兑换现金券
    public function exchangeCashCoupon($in){
        $url = '/cashcoupon/index/exchange';
        return HttpUtil::setP($in, $url);
    }
    //现金券详情
    public function cashCouponDetails($in){
        $url = '/cashcoupon/index/nindex';
        return HttpUtil::setP($in, $url);
    }

    public function postGiftUserRecord($in){
        $url = '/cashcoupon/index/fetch';
        return HttpUtil::setP($in, $url);
    }
}