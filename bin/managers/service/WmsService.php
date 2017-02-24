<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/21
 * Time: 16:06
 */
class WmsService extends Manager
{

    private static $templates_id = array(
        2 => 'OPENTM406271042', // order_success
        3 => '退款中',
        4 => '退款成功',
        5 => 'TM00010',  // 消费成功
    );


    // 通过模板编号获取模板内容 根据 $play_order_info 里的 pay_status 来进行判断
    public function getTemplateContent($data, $open_id, $type_id)
    {

        # 下单成功 2
        $data = array(
            "keynote1" => "",  // 订单名称
            "keynote2" => "",  // 消费金额
            "keynote3" => "",  // 验证码 不存在验证码的时候 请在玩翻天app订单中查看 一个订单提醒一次。若出现多个验证码的情况，验证码之间以逗号区隔开

        );

        # 商品消费 5
        $data = array(
            # cs 表示消费数据的时候会用到的数据
            'cs' => array(
                "product_type" => "订单名",
                "name"         => '', // 订单名称
                "account_type" => "验证码",
                "account"      => '', // 验证码
            )
        );

        # 活动消费 5
        $data = array(
            # cs 表示消费数据的时候会用到的数据
            'cs' => array(
                "product_type" => "订单名",
                "name"         => '', // 订单名称
                "account_type" => "订单编号",
                "account"      => '', // 订单编号，微信购买、app购买都显示
            )
        );

        $wechat_out_chain_vo = WechatOutChainVo::model()->find('type_id = :type_id', array(':type_id' => $type_id));

        $content = array(
            'touser'      => $open_id,
            'template_id' => self::$templates_id[$type_id],
            'url'         => $wechat_out_chain_vo->outchain,
        );

        switch ($type_id) {
            case 2:
                $content['data'] = array(
                    "first"    => "您好您的订单购买成功！",
                    "keynote1" => $data['keynote1'],  // 订单名称
                    "keynote2" => $data['keynote2'],  // 消费金额
                    "keynote3" => $data['keynote3'],  // 验证码
                );
                break;
            case 5: // 消费模板
                $content['data'] = array(
                    "first"       => "您好,您已经成功消费。",
                    "productType" => $data['cs']['product_type'],   // 商品类别
                    "name"        => $data['cs']['name'],           // 商品名称
                    "accountType" => $data['cs']['account_type'],   // 消费账户类型
                    "account"     => $data['cs']['account'],        // 商品名称
                    "time"        => TimeUtil::getNowDateTime(),    // 消费时间
                );
                break;
        }

        $content['data']['remark'] = $wechat_out_chain_vo->content;
        return $content;
    }


}