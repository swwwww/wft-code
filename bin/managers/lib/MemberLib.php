<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/11/14 0014
 * Time: 下午 2:54
 */
class MemberLib extends Manager{
    // 会员套餐
    public static function getFreeCoupon($in = null){
        $url = '/user/index/getVipSession';
        return HttpUtil::setP($in, $url);
    }
    
    //会员专区接口
    public static function areaLists($in = null){
        $url = '/user/index/memberindex';
        return HttpUtil::setP($in, $url);
    }
    // vip会员banner及客服初始化信息
    public static function init($in = null){
        $url = '/application/index/init';
        return HttpUtil::setP($in, $url);
    }

}