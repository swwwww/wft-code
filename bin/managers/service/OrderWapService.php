<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/10/15
 * Time: 14:45
 */
class OrderWapService extends Manager
{
    public function getOrderSn($param)
    {
        $pay_result = null;

        // get order sn
        $type = intval($param["type"]) ? intval($param["type"]) : 0;  // use type to judge ticket or activity / 0 or 1

        $user_vo = UserLib::getNowUser();
        // common param
        $uid               = $user_vo['uid'];
        $associates_ids    = $param['associates_ids'];  // json
        $name              = $param['name'] ? $param["name"] : 0;
        $address           = $param["address"] ? $param["address"] : 0;
        $message           = $param['message'] ? $param["message"] : 0;
        $phone             = $param["phone"];
        $pay_password      = $param["pay_password"];
        $cash_coupon_id    = intval($param['cash_coupon_id']) ? intval($param["cash_coupon_id"]) : 0;
        $use_account_money = intval($param['use_account_money']) ? intval($param["use_account_money"]) : 0;

        if ($type == 0) {
            // ticket
            $number             = intval($param['number']) ? intval($param["number"]) : 0;
            $order_id           = intval($param['order_id']) ? intval($param["order_id"]) : 0;
            $coupon_id          = intval($param['coupon_id']) ? intval($param["coupon_id"]) : 0;
            $group_buy          = intval($param['group_buy']) ? intval($param["group_buy"]) : 0;
            $use_score          = intval($param['use_score']) ? intval($param["use_score"]) : 0;
            $group_buy_id       = intval($param['group_buy_id']) ? intval($param["group_buy_id"]) : 0;
            $free_coupon_number = intval($param['free_coupon_number']) ? intval($param["free_coupon_number"]) : 0;

            $param      = array(
                'phone'              => $phone,
                'name'               => $name,
                'address'            => $address,
                'number'             => $number,
                'coupon_id'          => $coupon_id,
                'order_id'           => $order_id,
                'group_buy'          => $group_buy,
                'group_buy_id'       => $group_buy_id,
                'client_id'          => '',
                'cash_coupon_id'     => $cash_coupon_id,
                'use_score'          => $use_score,
                'message'            => $message,
                'pay_password'       => $pay_password,
                'use_account_money'  => $use_account_money,
                'associates_ids'     => $associates_ids,
                'free_coupon_number' => $free_coupon_number,
            );
            $pay_result = OrderWapLib::getPayTicketOrderSn($param);

        } else if ($type == 1) {
            // activity
            $charges    = $param['charges'];  // json
            $meeting_id = $param['meeting_id'];  // 集合方式id
            $coupon_id  = intval($param['coupon_id']) ? intval($param["coupon_id"]) : 0; // activity id
            $session_id = intval($param['session_id']) ? intval($param["session_id"]) : 0; // 场次 id

            $param      = array(
                'id'                => $coupon_id,
                'phone'             => $phone,
                'name'              => $name,
                'address'           => $address,
                'session_id'        => $session_id,
                'charges'           => json_encode($charges),
                'meeting_id'        => $meeting_id,
                'cash_coupon_id'    => $cash_coupon_id,
                'message'           => $message,
                'pay_password'      => $pay_password,
                'use_account_money' => $use_account_money,
                'associates_ids'    => $associates_ids,
            );
            $pay_result = OrderWapLib::getPayActivityOrderSn($param);
        }
        $param_str = json_encode($param);

        $order_sn  = $pay_result['data']['order_sn'];
        LogUtil::info("[OrderWapService][getOrderSn]{$uid}-{$type}-{$order_sn}\t" . json_encode($pay_result));

        return $pay_result;
    }

    // 三种不同的支付方式
    public function orderPay($param, $real_pay=0)
    {
        $order_sn = $param['order_sn'];
        $pay_type = $param["pay_type"];

        if ($param['res'] == 'common_pay') {//商品支付
            $user_vo = UserLib::getNowUser();
            $param['open_id'] = $user_vo['wechat_user']['open_id'];
            $param['paytype'] = $param['pay_type'];
            $res_temp = OrderWapLib::getOrderPayParam($param);
            $res      = $res_temp['data'];

            LogUtil::info("[OrderWapService][orderPay][pay]: {$res['status']}\t" . json_encode($res_temp));
        } else {//充值
            $res = $param['res'];
            LogUtil::info("[OrderWapService][orderPay][recharge]: {$res['status']}\t" . json_encode($param));
        }

        $order_pay_res = array();

        $order_pay_res['status'] = $res['status'];

        if ($pay_type == 1) {
            // alipay
            $ali_url     = str_replace("\"", "", $res['params']);
            $data_filter = self::dataFilter($ali_url);
            $ali_service = new AlipayService();

            $return_url = HttpUtil::getBaseHost();

            if ($param['type'] == 0) {
                // 商品返回跳转地址
                $return_url .= "/ticket/commodityOrder?share=1&order_sn=" . str_replace("WFT", '', $data_filter['out_trade_no']);
            } else if ($param['type'] == 1) {
                // 活动返回跳转地址
                $return_url .= "/orderWap/orderPlayDetail?share=1&order_sn=" . str_replace("WFT", '', $data_filter['out_trade_no']);
            } else if ($param['type'] == 9) {
                // 当前订单超过套餐金额
                $return_url .= "/user/remainAccount?share=1&total=".$data_filter['total_fee']."&package_info=".$param['package_info'];
            }

            $ali_url = $ali_service->alipayParam($data_filter['out_trade_no'], $data_filter['subject'], $data_filter['total_fee'], $data_filter['body'], $return_url);

            $data['status']       = 1;
            $data['url']          = $ali_url;
            $order_pay_res['msg'] = '正在跳转支付';
            $order_pay_res['data'] = $data;
            LogUtil::info("[OrderWapService][orderPay][ali_url]: {$data['url']}");

        } else if ($pay_type == 2) {
            // un

        } else if ($pay_type == 4) {
            // wechat
            // 返回前端页面必须参数
            if (false && 'old_wechat_pay_way'){
                $data = self::wechatPayment($param, $real_pay);
                $order_pay_res['data'] = $data;
                $status = $data['status'];
                if ($status) {
                    $order_pay_res['msg'] = "微信支付成功";
                } else {
                    $order_pay_res['msg'] = "微信预支付订单产生失败";
                }
                $order_pay_res['status'] = $status;
            }else{
                // 4.0后端产生预支付订单的方式
                $order_pay_res = array(
                    'data' => $res['pay_data'],
                    'msg' => $res['message'],
                    'status' => $res['status']
                );
                $order_pay_res['data']['status'] = $res['status'];
                $order_pay_res['data']['msg'] = $res['message'];
                $order_pay_res['data']['order_sn'] = $order_sn;
            }
        }
        $data['order_sn']      = $order_sn;
        $data['middleware']    = $res['middleware'];
        return $order_pay_res;
    }

    // 微信支付并返回前端页面必须参数,包含预支付流程
    public function wechatPayment($data, $real_pay)
    {
        $app_id     = Yii::app()->params['wechat']['appid'];
        $partner_id = Yii::app()->params['wechat']['PartnerID'];
        $notify_url = Yii::app()->params['wechat']['notify_url'];

        $wechat = new WechatPayService();

        $user_vo = UserLib::getNowUser();

        $open_id = $user_vo['wechat_user']['open_id'];

        // wechat authorization
        //        $wechat->getAuthorUrl($data['author_url']);

        // get order information
        if ($real_pay == 0){
            $order_vo = OrderInfoVo::model()->find("order_sn = :order_sn", array("order_sn" => $data['order_sn']));
            $real_pay = $order_vo['real_pay'];
            $coupon_name = $order_vo['coupon_name'];
        }else{
            $coupon_name = "玩翻天-充值";
            $rechage = true;
        }

        $params   = array(
            'app_id'      => $app_id,
            'open_id'     => $open_id,
            'partner_id'  => $partner_id,
            'nonce_str'   => StringUtil::getNonceStr(),
            'coupon_name' => $coupon_name,
            'notify_url'  => $notify_url,
            'order_sn'    => $data['order_sn'],
            'real_pay'    => $real_pay
        );

        // 调用预支付
        $pay_obj = $wechat->wechatPrepayment($params, $rechage);

        //LogUtil::info("[OrderWapService][wechatPayment][wechatPrepayment] \t order_sn \t app_id \t open_id \t prepay_id \t return_code \t return_msg");
        LogUtil::info("[OrderWapService][wechatPayment][wechatPrepayment]\t" . $data['order_sn'] . "\t" . $app_id . "\t" . $open_id . "\t" . $pay_obj->prepay_id . "\t" . $pay_obj->return_code . "\t" . $pay_obj->return_msg);
        LogUtil::info(json_encode($pay_obj));

        //{"return_code":"FAIL","return_msg":"JSAPI\u652f\u4ed8\u5fc5\u987b\u4f20openid"}
        if($pay_obj->return_code == 'FAIL'){
            LogUtil::trace("[OrderWapService][wechatPayment][wechatPrepayment]: {$user_vo['uid']}\t{$data['order_sn']}\t" . $pay_obj->return_msg);
        }

        $pay_data            = array(
            'appId'     => $app_id,
            'timeStamp' => time(),
            'nonceStr'  => StringUtil::getNonceStr(),// 随机字符串
            'package'   => 'prepay_id=' . $pay_obj->prepay_id,
            'signType'  => "MD5", //签名方式
            // 'paySign'=>''//签名
        );
        $pay_data['paySign'] = $wechat->getPaySignature($pay_data);

        if ($pay_obj->prepay_id) {
            $pay_data['status'] = 1;
        } else {
            $pay_data['status'] = 0;
        }

        return $pay_data;
    }

    public function dataFilter($data)
    {
        $data_first_exp = explode("&", $data);
        $res            = array();
        for ($i = 0; $i < count($data_first_exp); $i++) {
            $data_second_exp = explode("=", $data_first_exp[$i]);
            switch ($data_second_exp[0]) {
                case "body":
                    $res['body'] = $data_second_exp[1];
                    break;
                //                case "it_b_pay":
                //                    $res['it_b_pay'] = $data_second_exp[1];
                //                    break;
                case "notify_url":
                    $res['notify_url'] = $data_second_exp[1];
                    break;
                //                case "partner":
                //                    $res['partner'] = $data_second_exp[1];
                //                    break;
                //                case "payment_type":
                //                    $res['payment_type'] = $data_second_exp[1];
                //                    break;
                //                case "seller_id":
                //                    $res['seller_id'] = $data_second_exp[1];
                //                    break;
                //                case "service":
                //                    $res['service'] = $data_second_exp[1];
                //                    break;

                case "out_trade_no":
                    $res['out_trade_no'] = $data_second_exp[1];
                    break;
                case "subject":
                    $res['subject'] = $data_second_exp[1];
                    break;
                case "total_fee":
                    $res['total_fee'] = $data_second_exp[1];
                    break;
                //                case "sign":
                //                    $res['sign'] = $data_second_exp[1];
                //                    break;
                //                case "sign_type":
                //                    $res['sign_type'] = $data_second_exp[1];
                //                    break;
                default:
                    break;
            }
        }
        return $res;
    }



    public function getUserMoney($uid)
    {
        $account_vo = AccountVo::model()->find("uid = :uid", array(":uid" => $uid));
        if ($account_vo and $account_vo->status == 1) {  //判断状态
            $money = $account_vo->now_money;
        } else {
            $money= "0.00";
        }
        return (string)$money;  //客户端 多个小数位问题
    }
}