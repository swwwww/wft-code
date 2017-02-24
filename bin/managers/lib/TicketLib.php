<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/16
 * Time: 10:21
 */
class TicketLib extends Manager{
    //买票数据
    public static function tagList($in=null)
    {
        $url = '/tag/index';
        return HttpUtil::setP($in, $url);
    }

    //选择区域数据接口
    public static function areaList($in=null){
        $url = '/tag/index/area';
        return HttpUtil::setP($in, $url);
    }

    public function couponList($i = null){
        $url = '/coupon/list/index';
        return HttpUtil::setP($i, $url);
    }
    //商品详情数据
    public function commodityDetail($in = null){
        $url = '/good/index/nindex';
        return HttpUtil::setP($in, $url);
    }

    //取消点赞数据接口
    public function delPost($in=null){
        $url = '/social/sendpost/deletelike';
        return HttpUtil::setP($in,$url);
    }

    //点赞数据接口
    public function sendPost($in=null){
        $url = '/social/sendpost/like';
        return HttpUtil::setP($in,$url);
    }

    //咨询接口
    public function consult($in=null){
        $url = '/post/consult/index';
        return HttpUtil::setP($in,$url);
    }

    //二次评论页面接口
    public function recommend($in=null){
        $url = '/post/index/info';
        return HttpUtil::setP($in,$url);
    }

    //回复评论接口
    public function reply($in =null){
        $url = '/post/index/index';
        return HttpUtil::setP($in,$url);
    }

    //收藏按钮接口
    public function collect($in=null){
        $url ='/user/collect/update';
        return HttpUtil::setP($in,$url);
    }

    //套系选择数据
    public function commoditySelect($in = null){
        $url = '/good/index/nselectList';
        return HttpUtil::setP($in, $url);
    }

    //用户联系人数据接口
    public static function  addressList($in = null){
        $url = '/user/phone';
        return HttpUtil::setP($in, $url);
    }

    //设置默认联系地址接口
    public function defaultAddr($in = null){
        $url = '/user/phone/setdefault';
        return HttpUtil::setP($in,$url);
    }

    //删除联系地址
    public function delAddress($in = null){
        $url = '/user/phone/deletephone';
        return HttpUtil::setP($in,$url);
    }

    //编辑地址
    public function editAddress($in = null){
        $url = '/user/phone/editphone';
        return HttpUtil::setP($in,$url);
    }

    //日历时间控件
    public function calendar($in = null){
        $url = '/good/index/calendar';
        return HttpUtil::setP($in,$url);
    }
}