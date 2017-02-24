<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/25
 * Time: 13:56
 */
class OrderService extends Manager
{
    // 目前这里面的方法数据返回全部做return不直接使用out



    // 插入商品订单
    public function commodity($data)
    {
        $data_result = array(
            'status' => 1,
            'data'   => array(),
            'msg'    => '操作成功',
        );

        // 判断版本
        if ($data['order_type'] == 1) {
            // 版本错误 需要更新版本
            $data_result['status'] = 0;
            $data_result['msg'] = '请更新版本';
            return $data_result;
        }

        $model_pay_data = new PayData();
        $model_game_data = new GameData();
        $model_user_data = new UserData();
        $model_order_data = new OrderData();

        $data['real_buy_number'] = $model_order_data->getBuyNumberByGroupBuy($data);  // 获取最终购买数量
        $surplus = $data['count_number'] - $data['real_buy_number']; // 计算商品剩余数

        if ($data['real_buy_number'] > $surplus) {
            // 真实购买数量大于剩余数量
            $data_result['status'] = 0;
            $data_result['msg'] = '该商品目前数量不足';
            return $data_result;
        }

        // 对购买资格进行验证
        $check_param = array(
            'uid'               => $data['uid'],
            'qualify_coupon_id' => $data['qualify_coupon_id'],
            'order_sn'          => $data['order_sn'],
            'qualified'         => $data['organizer_game']['qualified'],
            'coupon_id'         => $data['coupon_id']
        );
        $check_result_buy_qualify = $model_user_data->checkBuyQualify($check_param);

        if ($check_result_buy_qualify['status'] == 0) {
//            HttpUtil::out($check_result_buy_qualify);
            return $check_result_buy_qualify;
        }
        ///////////////////////////////////////////////////////////////////////////////

        // 对现金券进行验证
        $check_param = array(
            'uid'            => $data['uid'],
            'pay_price'      => bcmul($data['buy_number'], $data['unit_price'], 2),
            'cash_coupon_id' => $data['cash_coupon_id'],
        );
        $check_result_cash_coupon = $model_user_data->checkCashCoupon($check_param);

        if ($check_result_cash_coupon['status'] == 0) {
//            HttpUtil::out($check_result_cash_coupon);
            return $check_result_cash_coupon;
        }
        ///////////////////////////////////////////////////////////////////////////////

        // 对积分进行验证
        $check_param = array(
            'uid'       => $data['uid'],
            'use_score' => $data['use_score'],
            'integral'  => $data['game_info']['integral'],
        );
        $check_result_integral_user = $model_user_data->checkIntegralUser($check_param);
        if ($check_result_integral_user['status'] == 0) {
            return $check_result_integral_user;
        }
        ///////////////////////////////////////////////////////////////////////////////

        // 进行团购判断
        $data_group_buy_id = $model_order_data->getGroupBuyId($data);

        if ($data_group_buy_id['status'] == 0) {
            return $data_group_buy_id;
        } else {
            $data['group_buy_id'] = $data_group_buy_id['data'];
        }

        $data_pay_count = bcmul($data['buy_number'], $data['unit_price'], 2);           // 支付总价
        $data['real_pay'] = bcsub($data_pay_count, $check_result_cash_coupon['price'], 2); // 计算现金券后剩余支付金额
        $data['cash_money'] = $check_result_cash_coupon['price'];                            // 记录代金券所付的金额

        // 获取套系时间地点信息
        $data_game_info = $model_game_data->getGameInfoById($data['game_info']['id']);

        // 订单信息生成的事务开始
        $transaction = Yii::app()->db->beginTransaction();
        if ($data['real_buy_number']) {
            // 更新售卖的数量
            $data_result_update_buy_number = $model_game_data->updateGameInfoBuyNumber($data['game_info']['id'], $data['real_buy_number']);

            if ($data_result_update_buy_number['status'] == 0) {
                // 售卖数量更新失败
                return $data_result_update_buy_number;
            }
        }

        // 进行订单信息的生成
        $data_result_insert_commodity = $model_order_data->insertCommodity($data);

        if ($data_result_insert_commodity['status']) {
            // 生成订单相关信息成功，提交事务
            $transaction->commit();
        } else {
            // 生成订单相关信息失败，事务回滚
            $transaction->rollback();
            return $data_result_insert_commodity;
        }
        ///////////////////////////////////////////////////////////////////////////////

        $order_sn = $data_result_insert_commodity['data'];
        if ($data['use_account_money']) { //使用账户余额支付
            // 进入订单支付
            $data_param_pay = array(
                'order_sn'      => $order_sn,
                'account_name'  => $data['uid'],
                'account_money' => $data['uid'],
                'trade_no'      => '',
                'pay_type'      => 'account'
            );
            $data_result_commodity_pay = $this->commodityPay($data_param_pay);

            if ($data_result_commodity_pay['status']) {
                $service_invite = new InviteService();
                $middleware = $service_invite->middleware($order_sn, 1);

                $data_result = array(
                    'status' => 1,
                    'msg'    => '下单成功',
                    'data'   => array(
                        'order_sn'     => $order_sn,
                        'group_buy_id' => $data['group_buy_id'],
                        'middleware'   => $middleware
                    )
                );
            } else {
                //订单生成成功,支付过程失败
                $data_result = array(
                    'status' => 0,
                    'msg'    => '账户支付失败'
                );
            }
        } else {
            $data_result = array(
                'status' => 1,
                'msg'    => '下单成功，等待支付',
                'data'   => array(
                    'order_sn'     => $order_sn,
                    'group_buy_id' => $data['group_buy_id']
                )
            );
        }
        return $data_result;
    }


    private function commodityPay($data)
    {
        $model_order_data = new OrderData();

        $action_result = array(
            'status' => 1,
            'msg'    => '执行失败'
        );

        if (empty($data['order_sn'])) {
            $action_result = array(
                'status' => 0,
                'msg'    => '订单编号缺失'
            );

            return $action_result;
        }

        if (empty($data['uid'])) {
            $action_result = array(
                'status' => 0,
                'msg'    => '用户信息缺失'
            );
            return $action_result;
        }

        $data_order_info = OrderData::getOrderInfoByOrderSn($data['order_sn']);
        $data_order_info_game = OrderData::getOrderInfoGameByOrderSn($data['order_sn']);

        if ($data_order_info['pay_status'] < 2) {
            $data_return_lib_order_pay = OrderLib::OrderPay($data, $data_order_info, 1);

            // 更新订单数据
            $data_order_info['real_pay'] = $data_return_lib_order_pay['data']['real_pay'];
            $data_order_info['account_money'] = $data_return_lib_order_pay['data']['account_money'];
            $data_order_info['pay_status'] = $data_return_lib_order_pay['data']['pay_status'];
        } else {
            $action_result = array(
                'status' => 0,
                'msg'    => '订单已处理'
            );
            return $action_result;
        }

        if ($data_return_lib_order_pay['status']) {
            $data_game_info = GameInfoVo::model()->find('id = :id', array(':id' => $data_order_info_game['game_info_id']))->getAttributes();

            $data_real_pay = $data_return_lib_order_pay['data']['real_pay'];      // 仍需要支付的金额
            $data_account_money = $data_return_lib_order_pay['data']['account_money']; // 账户支付的金额
            $data_total_money = bcadd($data_account_money, $data_real_pay, 2);       // 总的支付金额
            $data_use_time = date('Y-m-d', $data_game_info['end_time']);          // 使用时间

            $data_zyb_code = null;
            if ($data_game_info['goods_sm']) {
                $data_zyb_code = $model_order_data->postZyb($data['order_sn']);
            }

            // 发送支付完成消息
            OrderLib::sentPayMessage($data_order_info, $data_use_time, $data_zyb_code, 1);

            // 支付完成奖励积分和票券
            $service_integral = new IntegralService();
            $service_integral->getIntegralForBuyGood($data_order_info['user_id'], $data_order_info['order_sn'], $data_total_money, $data_order_info['order_city'], $data_order_info['coupon_name']);

            //奖励现金券
            $service_coupon = new CouponService();
            $service_coupon->getCashCouponByBuy($data_order_info['user_id'], $data_order_info['coupon_id'], $data_order_info_game['game_info_id'], $data_order_info['order_sn'], $data_order_info['order_city'], $data_order_info['coupon_name']);

            //返利
            $service_account = new AccountService();
            $service_account->getCashByBuy($data_order_info['user_id'], $data_order_info['coupon_id'], $data_order_info_game['game_info_id'], $data_order_info['order_sn'], $data_order_info['order_city'], $data_order_info['coupon_name']);
            CacheUtil::del('D:tneedPay:' . $data_order_info['user_id']);

            $action_result = array(
                'status' => 1,
                'msg'    => '订单已支付'
            );

            return $action_result;
        } else {
            $action_result = array(
                'status' => 0,
                'msg'    => '订单未支付成功'
            );
            return $action_result;
        }
    }

    public function commodityBack($data)
    {
        $data = array(
            'order_sn' => '',
            'code'     => '',
            'group'    => 1,
            'money'    => null,
        );

        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );

        $code = $data['code'];
        $order_sn = $data['order_sn'];
        $group = $data['group'];
        $money = $data['money'];

        $back_money = 0;
        $id = substr($code, 0, -7);
        $service = new OrderData();

        $order_data = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));

        if ($group == 2 && $order_data->coupon_unit_price < $money) {
            $result['status'] = 0;
            $result['msg'] = '指定退款金额不能高于原先价格';
            return $result;
        }

        $refund_time = $service->getRefundTime($order_data->order_type, $order_data->coupon_id);
        $zyb_data = $service->getZybData($order_sn, $id);

        if ($zyb_data) {
            if (!$zyb_data->zyb_code || $zyb_data->status != 2) {
                $result['status'] = 0;
                $result['msg'] = '操作失败';
                return $result;
            }

            // todo 调用api
            $ZybPay = new ZybService();
            $rest = $ZybPay->backPartTicket($id);

            if ($rest['code'] == '0' && $rest['description'] == '成功') {
                ZybInfoVo::model()->updateAll(array('status' => 3, 'back_number' => $rest['retreatBatchNo']), 'order_sn = :order_sn and code_id = :code_id', array(':order_sn' => $order_sn, ':code_id' => $id));
            } else {
                $result['status'] = 0;
                $result['msg'] = $rest['description'];
                return $result;
            }
        }
        // 设为退款中状态
        if ($group == 2 && $money) {
            $flag_coupon_code_update = $service->updateCouponCode($id, $order_sn, $money);
        } else {
            if ($refund_time < time() && $group == 1) {  //不可以退款,金额为0,客户端不会显示退款按钮 管理员添加退款 不受时间限制
                $back_money = 0;
            } else {
                //是否使用代金券
                if ($order_data->voucher_id) {
                    $cash_data = CashcouponUserLinkVo::model()->find('id = :id', array(':id' => $order_data->voucher_id));
                    if ($cash_data and $cash_data->is_back == 0) {

                        if ($cash_data->back_money != $order_data->voucher) {
                            $order_data->voucher = bcsub($order_data->voucher, $cash_data->back_money, 2); //剩余可退金额
                        }
                        if ($order_data->coupon_unit_price >= $order_data->voucher) {
                            //代金券小于当前金额,销毁代金券,退款扣除代金券后的剩余金额
                            $service->updateCashcouponUserLink($order_data->voucher_id, $order_data->voucher, 0);
                            $back_money = bcsub($order_data->coupon_unit_price, $order_data->voucher, 2);
                        } else {
                            //代金券大于当前金额,用户退款金额为0,记录代金券剩余金额以供下次扣除
                            $service->updateCashcouponUserLink($order_data->voucher_id, $order_data->coupon_unit_price, 1);
                            $back_money = 0;
                        }
                    } else {
                        $back_money = $order_data->coupon_unit_price;
                    }
                } else {
                    $back_money = $order_data->coupon_unit_price;
                }
            }
            $flag_coupon_code_update = $service->updateCouponCode($id, $order_sn, $back_money);
        }
        if (!$order_data or !$flag_coupon_code_update) {
            $result['status'] = 0;
            $result['msg'] = '操作失败';
            return $result;
        }
        $service->updateCouponCodeStatus($order_sn);

        // 恢复卡券购买数
        if ($order_data->order_type == 1) {
            $flag_coupon_update = $service->updateCoupons($order_data->coupon_id);
            $flag_organizer_game_update = true;
        } else {
            $game_info = OrderInfoGameVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
            $flag_coupon_update = $service->updateGameInfoBuy($game_info->game_info_id);
            $flag_organizer_game_update = $service->updateOrganizerGameBuyNum($order_data->coupon_id);
        }
        if ($refund_time < time() && $group == 1) {  //不可以退款，还原购买数，退款成功
            return $this->commodityBackSuccess($order_sn, $code, 1);
        }
        if ($flag_coupon_update and $flag_organizer_game_update) {
            //同玩商品 减去数量
            if ($order_data->group_buy_id) {
                $service->updateGroupByJoinNumber($order_data->group_buy_id);
            }

            // 记录操作日志
            $order_action_vo = new OrderActionVo();
            $order_action_vo->order_id = $order_sn;
            $order_action_vo->play_status = 3;
            $order_action_vo->action_user = $order_data->user_id;
            $order_action_vo->action_note = '退款中';
            $order_action_vo->dateline = time();
            $order_action_vo->action_user_name = ($group == 1) ? '用户' . $order_data->username : '管理员' . $_COOKIE['user'];
            $order_action_vo->save();

            if ($back_money > 0) { //如果退款金额是0 里面确认退款成功
                $sms_service = new SmsService();
                $sms_service->Send18($order_data->buy_phone, $code, $order_data->coupon_name, $order_data->order_city);
            } else {
                return $this->commodityBackSuccess($order_sn, $code, 1);
            }

            $order_info_vo = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
            $order_info_vo->backing_number += 1;
            $order_info_vo->save();

            $result['status'] = 1;
            $result['msg'] = '操作成功';
        } else {
            $result['status'] = 0;
            $result['msg'] = '操作失败';
        }
        return $result;
    }

    private function commodityBackSuccess($order_sn, $code, $group = 2)
    {
        // 正确使用
        $id = substr($code, 0, -7);
        $service = new OrderData();
        //订单信息
        $order_data = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
        $code_data = CouponCodeVo::model()->find('order_sn = :order_sn' and 'id = :id', array(':order_sn' => $order_sn, ':id' => $id));
        if (!$order_data or !$code_data) {
            $result['status'] = 0;
            $result['msg'] = '订单或验证码不存在';
            return $result;
        }

        // 根据订单号 成功退款

        $coupon_code_vo = CouponCodeVo::model()->find('order_sn = :order_sn and id = :id and status != 1', array(':order_sn' => $order_sn, ':id' => $id));
        $coupon_code_vo->status = 2;
        $coupon_code_vo->test_status = 2;
        $res = $coupon_code_vo->save();

        if (!$res) {
            $result['status'] = 0;
            $result['msg'] = '操作失败';
            return $result;
        }

        //检查除此之外是否还有等待退订的
        $res = $service->checkPayBackWaiting($order_sn);
        if ($res) {
            $rec = true;
            if ($code_data->back_money > 0) {

                // todo api
                $account = new AccountService();
                $rec = $account->recharge($order_data->user_id, $code_data->back_money, 1, '退款' . $order_data->coupon_name, 1, $order_sn);

                if ((int)$order_data->account_money == 0 && $rec) { //如果没有用账户付款 且往账号充钱了
                    $order_back_tmp_vo = new OrderBackTmpVo();
                    $order_back_tmp_vo->order_sn = $code_data->order_sn;
                    $order_back_tmp_vo->code_id = $code_data->id;
                    $order_back_tmp_vo->dateline = time();
                    $order_back_tmp_vo->last_dateline = time();
                    $order_back_tmp_vo->status = 1;
                    $order_back_tmp_vo->save();
                }
            }
            if (!$rec) {
                $result['status'] = 0;
                $result['msg'] = '充值失败';
                return $result;
            }

            // 记录操作日志
            $order_action_vo = new OrderActionVo();
            $order_action_vo->order_id = $order_data->order_sn;
            $order_action_vo->play_status = 4;
            $order_action_vo->action_user = $order_data->user_id;
            $order_action_vo->action_note = ($group == 1) ? "过了退款时间 用户执行退款, 退款成功" : "退款成功,卡券密码{$code},金额{$code_data->back_money}元";
            $order_action_vo->dateline = time();
            $order_action_vo->action_user_name = ($group == 1) ? '用户' . $order_data->username : '管理员' . $_COOKIE['user'];
            $order_action_vo->save();


            $message = "兑换码为{$code}的{$order_data->coupon_name}，已经退款完成，退款已返回至您的账户，请查看";

            $link_id_json = json_encode(array(
                'type' => (($order_data->order_type == 1) ? 'coupon' : 'game'),
                'id'   => $order_data->coupon_id,
                'lid'  => $order_data->order_sn
            ));

            $user_message_vo = new UserMessageVo();
            $user_message_vo->uid = $order_data->user_id;
            $user_message_vo->type = 8;
            $user_message_vo->title = '退款完成';
            $user_message_vo->deadline = time();
            $user_message_vo->message = $message;
            $user_message_vo->link_id = $link_id_json;

            if ($order_data) {//退款扣积分，只是记录，做任务时需要

                // todo api
                $integral = new IntegralService();
                $integral->returnGood($order_data->user_id, $order_data->order_sn, $order_data->order_city);
            }
            $result['status'] = 0;
            $result['msg'] = '退款成功';
            return $result;
        } else {
            $result['status'] = 0;
            $result['msg'] = '退款失败';
            return $result;
        }
    }

    // 插入活动订单
    public function activity($data)
    {
        $data = array(
            'event_data'        => '',
            'base_data'         => array(
                'id' => ''
            ),
            'charges'           => '',
            'user_data'         => array(
                'uid' => ''
            ),
            'associates_ids'    => '',
            'all_buy_number'    => '',
            'buy_name'          => '',
            'buy_phone'         => '',
            'buy_address'       => '',
            'cash_coupon_id'    => '',
            'use_account_money' => 0,
            'message'           => '',
            'meeting_id'        => 0,
            'city'              => 'WH',
            'share_order_sn'    => 0
        );

        // 只负责插入订单,现金券使用,积分使用,独立及支付后处理
        $real_pay = 0;  // 银行卡需要支付的金额 实付金额
        $cash_money = 0;// 现金券金额
        $buy_number = 0; // 订单总购买数
        $cash_coupon_id = $data['cash_coupon_id'];

        $service = new OrderData();

        foreach ($data['charges'] as $v) {
            $buy_number += $v['buy_number'];
            $real_pay = bcadd(bcmul($v['price'], $v['buy_number'], 2), $real_pay, 2);
        }
        $total_price = $real_pay;

        // 计算满减金额
        $exercise_event_vo = ExerciseEventVo::model()->find('id = :id', array(':id' => $data['event_data']['id']));
        if ($exercise_event_vo && $full_price = $exercise_event_vo->full_price && $less_price = $exercise_event_vo->less_price) {
//            // 数据库有数据的情况
//            if ($exercise_event_vo->welfare_type && $full_price <= $data['all_buy_number']){
//                $real_pay = bcsub($real_pay, $less_price, 2);
//            }elseif($full_price <= $real_pay){
//                $real_pay = bcsub($real_pay, $less_price, 2);
//            }
            $real_pay = bcsub($real_pay, $less_price, 2);
        }

        // 判断总金额 需要支付金额
        // 判断现金券
        $real_pay = bcsub($real_pay, $service->getCashCouponPrice($data['user_data']['uid'], $real_pay, $cash_coupon_id));

        // 主体事务
        $res_insert = $service->insertActivity($data, $real_pay, $cash_money, $buy_number, $total_price);
        $order_sn = $res_insert['order_sn'];
        // 给用户发送消息
        if (!$res_insert['status']) {
            $result['status'] = 0;
            $result['msg'] = '订单支付失败!';
            HttpUtil::out($result);
        }

        $share_order_sn = $data['share_order_sn'] > 0 ? $data['share_order_sn'] : 0;
        $res = $service->sendMessageAfterInviteSuccess($share_order_sn, $order_sn, $data['city'], $data['user_data']['uid']);

        if (!$res) {
            $result['status'] = 0;
            $result['msg'] = '返奖信息发送失败!';
            HttpUtil::out($result);
        }

        // 更新活动base总报名数量
        $exercise_base_vo = ExerciseBaseVo::model()->find('id = :id', array(':id' => $data['base_data']['id']));
        $exercise_base_vo->join_number += 1;
        $exercise_base_vo->save();

        // 订单已经成功生成
        $invite = new InviteService();
        $order_info = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));

        if ($data['use_account_money']) { //使用账户余额支付
            $pay_input = array(
                'order_info'   => $order_info,
                'trade_no'     => '',
                'pay_type'     => 'account',
                'account_name' => $data['uid']);
            // 进入支付
            $pay_status = $this->activityPay($pay_input);
            if ($pay_status) {
                $middleware = $invite->middleware($order_info, 1);
                $result['status'] = 2;
                $result['order_sn'] = $order_sn;
                $result['middleware'] = $middleware;
            } else {
                //订单生成成功,支付过程失败
                $result['status'] = 0;
                $result['msg'] = '账户支付失败';
            }
        } else {
            $middleware = $invite->middleware($order_info);
            $result['status'] = 1;
            $result['order_sn'] = $order_sn;
            $result['middleware'] = $middleware;
        }
        HttpUtil::out($result);
    }

    // 活动支付
    public function activityPay($data)
    {
        $data = array('order_sn' => '', 'trande_no' => '', 'pay_type' => 'account', 'accountName' => 'acc');

        $time = time();
        $lib = new OrderLib();

        $order_info = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $data['order_sn']));

        if ($order_info->pay_status < 2) {
            $res = $lib->OrderPay($time, $data, $order_info, 0);

            if ($res) {
                $real_pay = $res['real_pay'];
                $account_money = $res['account_money'];

                $total_money = bcadd($account_money, $real_pay, 2);  //账户加银行卡总支付金额
                // 发送支付完成消息
                // 判断用户是否有关注 玩翻天服务微信 如果有就微信推送 如果没有就发短信
                $lib->sentPayMessage($res['pay_status'], $order_info, $total_money);

                //支付完成奖励积分和票券
                $integral = new IntegralService();
                $integral->getIntegralForBuyGood($order_info->user_id, $order_info->order_sn, $total_money, $order_info->order_city, $order_info->coupon_name);
                //奖励现金券
                $coupon = new CouponService();
                $coupon->getCashCouponByBuy($order_info->user_id, $order_info->coupon_id, $order_info->coupon_id, $order_info->order_sn, $order_info->order_city, $order_info->coupon_name);
                //返利
                $cash = new AccountService();
                $cash->getCashByBuy($order_info->user_id, $order_info->coupon_id, $order_info->coupon_id, $order_info->order_sn, $order_info->order_city, $order_info->coupon_name);
            }
        } else {
            return false;
        }
        CacheUtil::del('D:tneedPay:' . $order_info->user_id);
        return true;
    }


    // 活动退款
    public function activityBack($order_sn, $code, $group = 1, $money = null)
    {
        //付款状态 ;0未付款;1付款中;2已付款 3  退款中 4 退款成功 5已使用 6已过期 7团购中
        //使用状态,0未使用,1已使用,2已退款,3退款中

        //订单信息
        $back_money = 0;
        $order_data = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
        $code_data = ExerciseCodeVo::model()->find('code = :code and order_sn = :order_sn', array(':code' => $code, ':order_sn' => $order_sn));

        if ($order_data->pay_status == 0) {
            return array('status' => 0, 'message' => '订单未付款');
        }
        if ($group == 2 && $code_data->price < $money) {
            return array('status' => 0, 'message' => '指定退款金额不能高于原先价格');
        }

        //获取退款时间
        $exe_event_vo = ExerciseEventVo::model()->find('id = :id', array(':id' => $order_data->coupon_id));
        $refund_time = $exe_event_vo->back_time;
        if (!$refund_time) {
            return array('status' => 0, 'message' => '允许退款时间未设置');
        }

        // 设置退款中状态
        $flag = false;
        if ($group == 2 && $money) {
            $exe_code_vo = ExerciseCodeVo::model()->find('order_sn = :order_sn and code = :code and status = 0', array(':order_sn' => $order_sn, ':code' => $code));
            $exe_code_vo->status = 3;
            $exe_code_vo->back_time = time();
            $exe_code_vo->back_money = $money;
            $flag = $exe_code_vo->save();
        } else {
            if ($refund_time < time() && $group == 1) {
//                $back_money = 0;
                return array('status' => 0, 'message' => '退款时间已过,退款失败');
            } else {
                if ($order_data->vocher_id) {
                    $cash_data = CashcouponUserVo::model()->find('id = :id', array(':id' => $order_data->vocher_id));
                    if ($cash_data && $cash_data->is_back == 0) {

                        if ($cash_data->back_money != $order_data->voucher) {
                            $order_data->voucher = bcsub($order_data->voucher, $cash_data->back_money, 2); //剩余可退金额
                        }
                        if ($code_data->price >= $order_data->voucher) {
                            //代金券小于当前金额,销毁代金券,退款扣除代金券后的剩余金额
                            $cash_data->is_back = 1;
                            $cash_data->back_money = $order_data->voucher;
                            $cash_data->save();
                            $back_money = bcsub($code_data->price, $order_data->voucher, 2);
                        } else {
                            $cash_data->back_money += $code_data->price;
                            //代金券大于当前金额,用户退款金额为0,记录代金券剩余金额以供下次扣除
                            $back_money = 0;
                        }
                    } else {
                        $back_money = $code_data->price;
                    }
                } else {
                    $back_money = $code_data->price;
                }
            }
            $exe_code_vo = ExerciseCodeVo::model()->find('order_sn = :order_sn and code = :code and status = 0', array(':order_sn' => $order_sn, ':code' => $code));
            $exe_code_vo->status = 3;
            $exe_code_vo->back_time = time();
            $exe_code_vo->back_money = $back_money;
            $flag = $exe_code_vo->save();
        }

        if (!$flag) {
            return array('status' => 0, 'message' => '操作失败');
        }

        //是否还有未使用的
        $un_use = ExerciseCodeVo::model()->findAll('order_sn = :order_sn and status=0', array(':order_sn' => $order_sn));
        $order_info = OrderInfoVo::model()->findAll('order_sn = :order_sn', array(':order_sn' => $order_sn));
        $order_info->backing_number += 1;
        if (!$un_use) {
            $order_info->pay_status = 3;
            $order_info->save();
        } else {
            $order_info->save();
        }

        // 恢复卡券购买数
        $exe_price_vo = ExercisePriceVo::model()->find('id = :id', array(':id' => $code_data->pid));
        $exe_price_vo->buy_number -= 1;
        $flag_exe = $exe_price_vo->save();
        if (!$flag_exe) {
            return array('status' => 0, 'message' => '操作失败3');
        }
        $exe_base_vo = ExerciseBaseVo::model()->find('id = :id', array(':id' => $code_data->bid));
        $exe_base_vo->join_number -= 1;
        $flag_exe = $exe_base_vo->save();
        if (!$flag_exe) {
            return array('status' => 0, 'message' => '操作失败3');
        }
        $exe_event_vo = ExerciseEventVo::model()->find('id = :id', array(':id' => $code_data->coupon_id));
        $exe_event_vo->join_number -= 1;
        $flag_exe = $exe_event_vo->save();
        if (!$flag_exe) {
            return array('status' => 0, 'message' => '操作失败3');
        }

        //查询是否所有已退订,删除对应出行人,如果存在的话
        $order_info_vo = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
        if ($order_info_vo->buy_number == ($order_info_vo->backing_number + $order_info_vo->back_number)) {
            $temp = OrderInsureVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
            $temp->delete();
        }

        // 记录操作日志
        $order_action_arr = array(
            'order_id'         => $order_sn,
            'play_status'      => 3,
            'action_user'      => $order_data->user_id,
            'action_note'      => '退款中',
            'dateline'         => time(),
            'action_user_name' => ($group == 1) ? '用户' . $order_data->username : '管理员' . $_COOKIE['user']
        );
        $order_action_vo = new OrderActionVo();
        $order_action_vo->commonSave($order_action_arr, $order_action_vo);

        if ($back_money > 0) { //如果退款金额是0 里面确认退款成功
            SmsService::Send18($order_data->buy_phone, $code, $order_data->coupon_name, $order_data->order_city);
        } else {
            return $this->activityBackSuccess($order_sn, $code, 1);
        }
        return array('status' => 1, 'message' => '操作成功');
    }


    /**
     * 成功退订
     *
     * @param $order_sn |订单号
     * @param $code     |完整的验证码
     * @param $group    |谁执行的退款 1 用户 (已过退款时间) 2 管理员
     *
     * @return array
     */
    public function activityBackSuccess($order_sn, $code, $group = 1)
    {

        //付款状态 ;0未付款;1付款中;2已付款 3  退款中 4 退款成功 5已使用 6已过期 7团购中
        //使用状态,0未使用,1已使用,2已退款,3退款中

        //订单信息
        $order_data = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
        $code_data = ExerciseCodeVo::model()->find('code = :code and order_sn = :order_sn', array(':code' => $code, ':order_sn' => $order_sn));

        if (!$order_data or !$code_data) {
            return array('status' => 0, 'message' => '订单或验证码不存在');
        }

        // 根据订单号 成功退款
        if ($code_data->status != 1) {
            $code_data->status = 2;
            if (!$code_data->save()) {
                return array('status' => 0, 'message' => '操作失败1');
            }
        }

        //检查除此之外是否还有等待退订的
        $exe_code_vo = ExerciseCodeVo::model()->findAll('order_sn = :order_sn and status = :status', array(':order_sn' => $order_sn, ':status = 3'));
        $order_info_vo = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));

        if ($exe_code_vo) {
            $order_info_vo->back_number += 1;
            $order_info_vo->backing_number -= 1;
            $order_info_update_flag = $order_info_vo->save();
        } else {
            $order_info_vo->pay_status = 4;
            $order_info_vo->back_number += 1;
            $order_info_vo->backing_number = 0;
            $order_info_update_flag = $order_info_vo->save();
        }
        if ($order_info_update_flag) {

            //如果是使用账户购买
            if ($code_data->back_money > 0 and $order_data->account_money > 0) {
                $account = new AccountService();
                $rec = $account->recharge($order_data->user_id, $code_data->back_money, 1, '退款' . $order_data->coupon_name, 1, $order_sn);
                if (!$rec) {
                    return array('status' => 0, 'message' => '退款失败');
                }
            }

            // 记录操作日志
            $order_action_vo = new OrderActionVo();
            $order_action_data = array(
                'order_id'         => $order_data->order_sn,
                'play_status'      => 4,
                'action_user'      => $order_data->user_id,
                'action_note'      => ($group == 1) ? "退款是0元 直接退款成功" : "退款成功,卡券密码{$code},金额{$code_data->back_money}元",
                'dateline'         => time(),
                'action_user_name' => ($group == 1) ? '用户' . $order_data->username : '管理员' . $_COOKIE['user']
            );
            $order_action_vo->commonSave($order_action_data);

            SmsService::Send9($order_data->buy_phone, $order_data->coupon_name, $order_data->order_city);

            $message = "兑换码为{$code}的{$order_data->coupon_name}，已经退款完成，退款已返回至您支付的账户，请查看";
            $user_message_vo = new UserMessageVo();
            $user_message_data = array(
                'uid'      => $order_data->user_id,
                'type'     => 8,
                'title'    => '退款完成',
                'deadline' => time(),
                'message'  => $message,
                'link_id'  => json_encode(array(
                    'type' => 'kidsplay',
                    'id'   => $order_data->coupon_id,
                    'lid'  => $order_data->order_sn
                ))
            );
            $user_message_vo->commonSave($user_message_data);

            if ($order_data) {//退款扣积分，只是记录，做任务时需要
                $integral = new IntegralService();
                $integral->returnGood($order_data->user_id, $order_data->order_sn, $order_data->order_city);
            }
            return array('status' => 1, 'message' => '退款成功');
        } else {
            return array('status' => 0, 'message' => '退款失败');
        }
    }

}
