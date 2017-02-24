<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/9
 * Time: 17:03
 */

class OrderLib extends Manager
{
    /**
     * 根据uid获取用户的余额信息
     * @param   data            支付相关参数
     * @param   order_info_vo   订单数据
     * @param   type            支付类型  1为商品，0为活动
     * @return
     */
    // 支付操作
    public static function OrderPay($data, $order_info, $type) {
        $model_userData      = new UserData();
        $time                = time();

        $transaction         = Yii::app()->db->beginTransaction();
        $data_account        = $model_userData->getUserAccount(array('uid' => $order_info->user_id));

        $trade_no            = $data['trade_no'];           // 支付流水编号
        $real_pay            = $order_info['real_pay'];       // 最终待付金额
        $account_money       = $order_info['account_money'];  // 账户需要支付金额
        $can_back_money_flow = 0;

        if ($data['pay_type'] == 'account') {
            // 操作用户账户余额数据
            if ($data_account->now_money >= $real_pay) {
                // 当前账户可支付的金额 大于 最终待付金额
                $data_user_account_param = array(
                    'uid'          => $data['account_name'],
                    'order_sn'     => $order_info['order_sn'],
                    'order_info'   => $order_info,
                    'account_data' => $data_account
                );
                $data_result_update_account = $model_userData->updateUserAccount($data_user_account_param);

                if ($data_result_update_account['status']) {
                    // 更新 账户付款使用码状态为审核
                    if ($type == 1) {
                        $vo_coupon_code = CouponCodeVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_info['order_sn']));
                        $vo_coupon_code->check_status = 2;
                        $vo_coupon_code->save();
                    }
                    $account_money = $real_pay;
                    $real_pay = 0;
                    $trade_no = $data_result_update_account['data'];
                } else {
                    $transaction->rollback();
                    $action_result = array(
                        'status' => 0,
                        'msg'    => '用户余额支付失败'
                    );
                    return $action_result;
                }
            }
        }

        $pay_status = 2;
        if ($type == 1) {
            if ($order_info['group_buy_id']) {
                $vo_group_buy = GroupBuyVo::model()->find('id = :id', array(':id' => $order_info['group_buy_id']));
                if ($vo_group_buy->join_number + 1 == $vo_group_buy->limit_number) {
                    // 当前团人数加一后 成团
                    $vo_group_buy->join_number += 1;
                    $vo_group_buy->status       = 2;
                    $data_return_group_buy      = $vo_group_buy->save();

                    $vo_order_info_group_buy = OrderInfoVo::model()->find('group_buy_id = :group_buy_id and pay_status = 7', array(':group_buy_id' => $order_info['group_buy_id']));
                    $vo_order_info_group_buy->order_status = 1;
                    $vo_order_info_group_buy->pay_status   = $pay_status;
                    $data_return_order_info_group_buy      = $vo_order_info_group_buy->save();

                    if (!$data_return_group_buy || !$data_return_order_info_group_buy) {
                        $transaction->rollback();
                        $action_result = array(
                            'status' => 0,
                            'msg'    => '团购数据更新失败'
                        );
                        return $action_result;
                    }
                } else {
                    $pay_status = 7;
                    $vo_group_buy->join_number += 1;
                    $data_return_group_buy = $vo_group_buy->save();
                    if (!$data_return_group_buy) {
                        $transaction->rollback();
                        $action_result = array(
                            'status' => 0,
                            'msg'    => '团购数据更新失败'
                        );
                        return $action_result;
                    }
                }
            }
        }

        // order_info 可能有数据更新
        $approve_status = ($data['pay_type'] == 'account') ? 2 : 1;
        $vo_order_info_update = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $data['order_sn']));
        $vo_order_info_update->account_type   = $data['pay_type'];
        $vo_order_info_update->order_status   = 1;
        $vo_order_info_update->pay_status     = $pay_status;
        $vo_order_info_update->trade_no       = $trade_no;
        $vo_order_info_update->account        = $data['account_name'];
        $vo_order_info_update->real_pay       = $real_pay;
        $vo_order_info_update->account_money  = $account_money;
        $vo_order_info_update->approve_status = $approve_status;
        $data_result_order_info_update = $vo_order_info_update->save();
        if (!$data_result_order_info_update) {
            $transaction->rollback();
            $action_result = array(
                'status' => 0,
                'msg'    => '订单数据更新失败'
            );
            return $action_result;
        }

        // 写入操作记录
        $vo_order_action = new OrderActionVo();
        $vo_order_action->order_id         = $order_info['order_sn'];
        $vo_order_action->play_status      = 2;
        $vo_order_action->action_user      = $order_info['user_id'];
        $vo_order_action->action_note      = '支付成功';
        $vo_order_action->dateline         = $time;
        $vo_order_action->action_user_name = '用户' . $order_info['username'];
        $data_result_order_action = $vo_order_action->save();
        if (!$data_result_order_action) {
            $transaction->rollback();
            $action_result = array(
                'status' => 0,
                'msg'    => '订单操作记录写入失败'
            );
            return $action_result;
        }
        $transaction->commit();
        $action_result = array(
            'status' => 1,
            'msg'    => '订单数据更新失败',
            'data'   => array(
                'account_money' => $account_money,
                'real_pay'      => $real_pay,
                'pay_status'    => $pay_status
            )
        );
        return $action_result;
    }

    /**
     * 支付后发送消息
     * @param   order_info /订单信息
     * @param   use_time   /可使用时间
     * @param   zyb_code   /智游宝券码
     * @param   type       /卡券类型
     * @return
     */
    public static function sentPayMessage($order_info, $use_time = 0, $zyb_code = 0, $type = 0) {
        $model_orderData         = new OrderData();
        $mongo_social_chat_msg   = new SocialChatMsgMongo();
        $mongo_social_circle_msg = new SocialCircleMsgMongo();

        $data_message     = "您购买的{$order_info['coupon_name']}已支付完成，请于{$use_time}之前使用。"; // 应用通知内容
        $data_total_money = bcadd($order_info['account_money'], $order_info['real_pay'], 2);       // 总的支付金额

        if ($order_info['group_buy_id'] != 0) {
            $data_group_buy_info = GroupBuyVo::model()->find('id = :id', array(':id' => $order_info['group_buy_id']));
        }

        if ($order_info['pay_status'] == 2) {
            if ($type == 1) {
                if ($order_info['group_buy_id'] != 0) {
                    // 团购成功 群发短信  后期 异步
                    $vo_order_info = OrderInfoVo::model()->findAll(
                        'group_buy_id = :group_buy_id and order_status = :order_status and pay_status = :pay_status',
                        array(':group_buy_id' => $order_info['group_buy_id'], ':order_status' => 1, ':pay_status' => 2)
                    );
                    foreach ($vo_order_info as $val) {
                        $data_code = CouponCodeVo::model()->findAll('order_sn = :order_sn', array(':order_sn' => $val->order_sn));
                        $data_code_len = '';
                        foreach ($data_code as $v) {
                            if ($data_code_len) {
                                $data_code_len .= ',(' . $v->id . $v->password . ')';
                            } else {
                                $data_code_len .= '('  . $v->id . $v->password . ')';
                            }
                        }
                        if ($zyb_code) {
                            SmsService::Send13($order_info['buy_phone'], $order_info['coupon_name'], $data_total_money, $zyb_code);
                        } else {
                            SmsService::Send4 ($order_info['buy_phone'], $order_info['coupon_name'], $data_total_money, $data_code_len);
                        }
                    }

                    // 发送通知消息
                    $model_orderData->sendMes($order_info['user_id'], $data_message, json_encode(array('type' => 'group', 'id' => $order_info['coupon_id'], 'lid' => (string)$order_info['order_sn'], 'group_buy_id' => $order_info['group_buy_id'])));

                    // 更新圈子内分享的数量
                    $data_return_social_chat_msg = SocialCircleMsgMongo::model()->updateAll(
                        array(
                            'object_data.join_number' => $data_group_buy_info->limit_number
                        ),
                        'object_data.group_buy_id = :group_buy_id',
                        array(
                            ':group_buy_id' => (int)$order_info['group_buy_id']
                        )
                    );

                    // 跟新消息数量
                    $data_return_social_chat_msg = SocialChatMsgMongo::model()->updateAll(
                        array(
                            'object_data.join_number' => $data_group_buy_info->limit_number
                        ),
                        'object_data.group_buy_id = :group_buy_id',
                        array(
                            ':group_buy_id' => (int)$order_info['group_buy_id']
                        )
                    );

                    //成团奖励
                    $pdo       = Yii::app()->db;
                    $sql       = "SELECT * FROM play_welfare WHERE object_type = 3 AND object_id = :object_id AND good_info_id = :good_info_id LIMIT 1";
                    $sql_param = array(
                        'object_id'    => $data_group_buy_info->game_id,
                        'good_info_id' => $data_group_buy_info->game_info_id
                    );
                    $data_return_welfare = $pdo->createCommand($sql)->query($sql_param);

                    if ($data_return_welfare) {
                        $coupon = new CouponService();
                        $coupon->addCashcoupon($data_group_buy_info->uid, $data_return_welfare['welfare_link_id'], $order_info['order_sn'], 5, 0, '团长奖励', $order_info['order_city']);
                    }
                } else {
                    //当前订单兑换码
                    $data_code = CouponCodeVo::model()->findAll('order_sn = :order_sn', array(':order_sn' => $order_info['order_sn']));
                    $data_code_len = '';
                    foreach ($data_code as $v) {
                        if ($data_code_len) {
                            $data_code_len .= ',(' . $v->id . $v->password . ')';
                        } else {
                            $data_code_len .= '('  . $v->id . $v->password . ')';
                        }
                    }
                    $model_orderData->sendMes($order_info['user_id'], $data_message, json_encode(array('type' => (($order_info['order_type'] == 1) ? 'coupon' : 'game'), 'id' => $order_info['coupon_id'], 'lid' => $order_info['order_sn'])));

                    if ($zyb_code) {
                        SmsService::Send13($order_info['buy_phone'], $order_info['coupon_name'], $data_total_money, $zyb_code);
                    } else {
                        SmsService::Send4 ($order_info['buy_phone'], $order_info['coupon_name'], $data_total_money, $data_code_len);
                    }
                }
            } else {
                // 活动消息发送
                $data_message = "您购买的{$order_info['coupon_name']}已支付完成";
                //当前订单兑换码
                $data_code = ExerciseCodeVo::model()->findAll('order_sn = :order_sn', array(':order_sn' => $order_info['order_sn']));
                $data_code_len = '';
                foreach ($data_code as $v) {
                    if ($data_code_len) {
                        $data_code_len .= ',(' . $v->code . ')';
                    } else {
                        $data_code_len .= '('  . $v->code . ')';
                    }
                }
                $model_orderData->sendMes($order_info['user_id'], $data_message, json_encode(array('type' => 'kidsplay', 'id' => $order_info['coupon_id'], 'lid' => $order_info['order_sn'])));
                SmsService::Send4($order_info['buy_phone'], $order_info['coupon_name'], $data_total_money, $data_code_len);
            }
        } elseif ($order_info['pay_status'] == 7) {
            SmsService::Send15($order_info['buy_phone'], $order_info['coupon_name'], $data_group_buy_info->limit_number);
            // 我的消息
            $model_orderData->sendMes($order_info['user_id'], $data_message, json_encode(array('type' => 'group', 'id' => $order_info['coupon_id'], 'lid' => (string)$order_info['order_sn'], 'group_buy_id' => $order_info['group_buy_id'])));

            // 更新圈子内分享的数量 +1
            $data_return_social_chat_msg = SocialCircleMsgMongo::model()->updateCounters(
                array(
                    'object_data.join_number' => 1
                ),
                'object_data.group_buy_id=:group_buy_id',
                array(
                    ':group_buy_id' => (int)$order_info['group_buy_id']
                )
            );

            // 跟新消息数量 +1
            $data_return_social_chat_msg = SocialChatMsgMongo::model()->updateCounters(
                array(
                    'object_data.join_number' => 1
                ),
                'object_data.group_buy_id=:group_buy_id',
                array(
                    ':group_buy_id' => (int)$order_info['group_buy_id']
                )
            );
        }
    }
}
