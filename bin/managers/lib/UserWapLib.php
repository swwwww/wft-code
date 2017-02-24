<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/17
 * Time: 16:36
 */
class UserWapLib extends Manager
{

    //个人中心 my_center
    public static function getUserInfo()
    {
        return HttpUtil::setP(null, '/user/info');
    }

    // 验证密码
    public static function verifyPassword($in)
    {
        $url = '/user/account/verifyPassword';
        return HttpUtil::setP($in, $url);
    }

    // 验证验证码
    public static function verifyCode($in)
    {
        $url = '/user/account/verifyCode';
        return HttpUtil::setP($in, $url);
    }

    // 更新密码
    public static function updatePassword($in)
    {
        $url = '/user/account/updatePayPassword';
        return HttpUtil::setP($in, $url);
    }

    // 找回/重置 密码
    public static function editPassword($in)
    {
        $url = '/user/account/setPayPassword';
        return HttpUtil::setP($in, $url);
    }

    // 我的验证码 code_sn
    public static function getUserCode($in)
    {
        $url = '/user/orderlist/codeinfo';
        return HttpUtil::setP($in, $url);
    }

    // 获取验证码
    public static function getCode($in)
    {
        $url = '/user/login/getCode';
        return HttpUtil::setP($in, $url);
    }

    //订单详情 my_order
    public static function getOrderList($in)
    {
        $url = '/user/orderlist';
        return HttpUtil::setP($in, $url);
    }

    //删除除订单接口
    public static function delOderItem($in)
    {
        $url = '/pay/excercise/cancelOrder';
        return HttpUtil::setP($in, $url);
    }

    //我的积分
    public function getUserPoints($data)
    {
        $url = '/score/index';
        return HttpUtil::setP($data, $url);
    }

    //签到获得我的积分
    public function getMyScore($data)
    {
        $url = '/user/index/signin';
        return HttpUtil::setP($data, $url);
    }

    //个人账户
    public function  getUserAccount($data)
    {
        $url = '/user/account/index';
        return HttpUtil::setP($data, $url);
    }

    //秒杀资格
    public static function getUserSeckill($data)
    {
        $url = '/score/index/seckill';
        return HttpUtil::setP($data, $url);
    }

    //积分兑换秒杀机会
    public function ExchangeScore($data)
    {
        $url = '/score/index/exchange';
        return HttpUtil::setP($data, $url);
    }

    //添加我的baby
    public function AddMyBaby($data)
    {
        $url = '/user/info/baby';
        return HttpUtil::setP($data, $url);
    }

    //上传图片
    public static function uploadImg($data)
    {
        $url = '/user/info/memberImg';
        return HttpUtil::setP($data, $url);
    }

    //销售员中心
    public static function sellerCenter($in)
    {
        $url = '/user/sell/info';
        return HttpUtil::setP($in, $url);
    }

    //分销提现接口
    public function getCash($in)
    {
        $url = '/user/sell/withdraw';
        return HttpUtil::setP($in, $url);
    }

    //分销商品活动列表
    public static function sellGoods($in)
    {
        $url = '/user/sell/goods';
        return HttpUtil::setP($in, $url);
    }

    //我的收藏列表
    public static function userCollect($in)
    {
        $url = '/user/collect/shopList';
        return HttpUtil::setP($in, $url);
    }

    //修改/设置支付密码
    public function setPassword($in)
    {
        $url = '/user/account/updatePassword';
        return HttpUtil::setP($in, $url);
    }

    public static function chargeMoney($in){
        $url = '/user/account/recharge';
        return HttpUtil::setP($in, $url);
    }

    public static function userMessage($in){
        $url = '/user/message/nindex';
        return HttpUtil::setP($in, $url);
    }

    // 修个个人信息
    public static function userInfoReset($in){
        $url = '/user/info/resetNew';
        return HttpUtil::setP($in, $url);
    }

    //我想去
    public static function userWantGo($in = null){
        $url = '/kidsplay/index/wantGoListForUser';
        return HttpUtil::setP($in, $url);
    }
    //我想去提醒列表
    public static function userRemindGo($in = null){
        $url = '/kidsplay/index/wantGoMessageForUser';
        return HttpUtil::setP($in, $url);
    }

    //存储卡
    public static function rechargeCard($in){
        $url = '/user/account/rechargeByCard';
        return HttpUtil::setP($in, $url);
    }
}

