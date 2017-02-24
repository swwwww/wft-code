<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/16
 * Time: 14:10
 */
class SmsService extends Manager
{
    private static function getWechatName($city){
        if ($city == 'NJ') {
            return "Wanfantiannanjing";
        } else {//武汉
            return "wanfantian1";
        }
    }

    public function getUserAuthCode($phone, $time)
    {
        $sql = "select * from play_auth_code
                where phone = {$phone}
                and play_auth_code.time > {$time}
                order by id desc";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    /**
     * 检验验证码
     *
     * @param $phone
     * @param $code
     * @param $type
     *
     * @return bool
     * author: MEX | mixmore@yeah.net
     */
    public function checkCode($phone, $code, $type = 0)
    {
        $play_auth_code = AuthCodeVo::model()->find('phone = :phone and code = :code and time < :time and type = :type', array(
            ':phone' => $phone,
            ':code' => $code,
            ':time' => time() + 60 * 5,
            ':type' => $type,
        ));
        if ($play_auth_code) {
            // $play_auth_code->status = 1;
            // $play_auth_code->save();
            return true;
        } else {
            return false;
        }
    }

    //通用 付款成功
    public static function Send4($phone, $coupon_name, $pay_money, $code_len)
    {
        $content = "“玩翻天”亲爱的小玩家，您购买的玩翻天商品\"{$coupon_name}\"已支付成功，付款金额为{$pay_money}元，验证码为{$code_len}，祝您遛娃愉快！立即添加微信号：wanfantian1，即可免费获取私人专享客户服务。";
        return SmsUtil::sendMessage($phone, $content);
    }

    //活动 退款时提醒（可退款）
    public static function Send9($phone, $coupon_name,$city='WH')
    {
        //$content = "“玩翻天”亲爱的小玩家，您已取消参加\"{$coupon_name}\"，款项已退回至玩翻天账户，在退款订单详情页面可申请原路退回。立即添加微信号：".self::getWeixinName($city)."，即可免费获取私人专享客户服务。";

        $content = "“玩翻天”亲爱的小玩家，您已取消参加\"{$coupon_name}\"，款项已退回至支付账户。立即添加微信号：".self::getWechatName($city)."，即可免费获取私人专享客户服务。";
        return SmsUtil::sendMessage($phone, $content);
    }

    //预约成功
    public static function Send13($phone, $coupon_name,$code_number,$zyb_code)
    {
        $content = "“玩翻天”亲爱的小玩家，您购买的 \"{$coupon_name}\"{$code_number}张已预约成功，请您凭辅助码【$zyb_code】换取门票";
        return SmsUtil::sendMessage($phone, $content);
    }

    //团主支付成功短信
    public static function Send15($phone, $coupon_name,$limit_number)
    {
        $content = "“玩翻天”亲爱的小玩家，你已支付了商品\"{$coupon_name}\"{$limit_number}人团的团费，2小时内集齐所有小玩家，就可成功购买，如果组团不成功，已经支付的钱会直接返回你的玩翻天余额账户。";
        return SmsUtil::sendMessage($phone, $content);
    }

    //商品提交退款短信 活动提交退款
    public static function Send18($phone, $code, $coupon_name,$city='WH')
    {
        $content = "亲爱的小玩家，您购买的\"{$coupon_name}\", 兑换码为\"{$code}\"的退订申请小玩已收到，我们将及时为您办理。立即添加微信号：".self::getWechatName($city)."，即可免费获取私人专享客户服务。";
        return SmsUtil::sendMessage($phone, $content);
    }

}