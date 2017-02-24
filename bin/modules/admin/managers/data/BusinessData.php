<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/2/17 0017
 * Time: 下午 12:01
 */
class BusinessData extends Manager
{

    //登陆接口
    public static function postBusinessLogin($in = null)
    {
        $url = '/selleradmin/account/login';
        $result = HttpUtil::proxyBusiness($in, $url, 'post', null);
        return $result;
    }
    //退出登录 /selleradmin/account/logOut
    public static function getLoginOut($in = null){
        $url = '/selleradmin/account/logOut';
        $result = HttpUtil::proxyBusiness($in, $url, 'post', null);
        return $result;
    }
    //商家后台首页
    public static function postIndex($in = null)
    {
        $url = '/selleradmin/index/index';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }

    //验证码查询接口
    public static function checkCode($in = null)
    {
        $url = '/selleradmin/order/codeInfo';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }

    //确认验证验证码接口
    public static function confirmCode($in = null)
    {
        $url = '/selleradmin/order/use';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }


    //忘记密码发送验证码
    public static function getPhoneCode($in = null)
    {
        $url = '/selleradmin/account/forgetPhoneCode';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }

    //忘记密码，找回密码接口
    public static function getLoginPassword($in = null)
    {
        $url = '/selleradmin/account/forgetPass';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }

    //修改密码接口
    public static function changePassword($in = null)
    {
        $url = '/selleradmin/account/changePass';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }

    //银行卡信息
    public static function getCardInfo($in = null)
    {
        $url = '/selleradmin/account/cardinfo';
        $result = HttpUtil::proxyBusiness($in, $url, 'post', null);
        return $result;
    }
    //修改银行卡信息
    public static function postUpdateCardInfo($in = null){
        $url = '/selleradmin/account/updatecard';
        $result = HttpUtil::proxyBusiness($in, $url, 'post', null);
        return $result;
    }

    //商家入驻
    public static function postMerchantInfo($in = null)
    {
        $url = '/selleradmin/account/join';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }

    //订单管理
    public static function postOrderList($in = null)
    {
        $url = '/selleradmin/order/codelist';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);

    }

    //导出
    public static function getOrderFile($in = null)
    {
        $url = '/selleradmin/order/outorder';
        return HttpUtil::proxyBusiness($in, $url, 'get', null);

    }
    //商家流水（财务管理）
    public static function postTradeFlow($in = null)
    {
        $url = '/selleradmin/order/log';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }
    //商品详情接口
    public static function getGoodDetails($in = null)
    {
        $url = '/selleradmin/coupon/info';
        return HttpUtil::proxyBusiness($in, $url, 'post', null);
    }
    //商品套系
    public static function getGoodsSeries($in = null)
    {
        $url = '/selleradmin/coupon/priceinfo';
        $result = HttpUtil::proxyBusiness($in, $url, 'post', null);
        if(isset($res['data'])){
            $list = $res['data'];
            $res['data'] = '';
            $res['data']['goods_list'] = $list;
        }
        return $result;
    }

}