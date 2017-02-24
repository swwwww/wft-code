<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/26
 * Time: 09:39
 */
class PayData extends Manager {
    public function commodityOrder ($order_sn, $trade_no = '', $pay_type, $uid) {
        $action_result = array(
            'status' => 1,
            'msg'    => '执行失败'
        );

        if (empty($order_sn)) {
            $action_result = array(
                'status' => 0,
                'msg'    => '订单编号缺失'
            );

            return $action_result;
        }

        if (empty($uid)) {
            $action_result = array(
                'status' => 0,
                'msg'    => '用户信息缺失'
            );
            return $action_result;
        }

        $data_order_info      = OrderData::getOrderInfoByOrderSn ($order_sn);
        $data_order_info_game = OrderData::getOrderInfoGameByOrderSn ($order_sn);

        $real_pay = $order_info->real_pay;  //银行卡金额
        $account_money = $order_info->account_money; //账户金额

        // 判断是否已经处理过
        if ($order_info->pay_status < 2) {
            //使用账户余额支付
            if ($pay_type == 'account') {
                $account_data = $adapter->query("select * from play_account where uid=?", array($order_info->user_id))->current();
                if (!$account_data || !$account_data->status) {
                    $conn->rollback();
                    return false;
                }
                //用哪个账户的钱,优先不可提现账户
                $can_back_money_flow = null;
                if (($account_data->now_money - $order_info->real_pay) < $account_data->can_back_money) {
                    $s9 = $adapter->query("UPDATE play_account SET now_money=now_money-{$order_info->real_pay},can_back_money=now_money,last_time=? WHERE uid=? AND now_money>={$order_info->real_pay}", array(time(), $order_info->user_id))->count();
                    $can_back_money_flow = $order_info->real_pay - ($account_data->now_money - $account_data->can_back_money);
                } else {
                    $s9 = $adapter->query("UPDATE play_account SET now_money=now_money-{$order_info->real_pay},last_time=? WHERE uid=? AND now_money>={$order_info->real_pay}", array(time(), $order_info->user_id))->count();
                }

                $coupon_name = '购买商品' . $order_info->coupon_name ?: '';
                $s10 = $adapter->query("INSERT INTO play_account_log (id,uid,action_type,action_type_id,object_id,flow_money,surplus_money,dateline,description,status,user_account,check_status,can_back_money_flow) VALUES (NULL ,?,?,?,?,?,?,?,?,?,?,?,?)",
                    array($order_info->user_id, 2, 1, $order_info->order_sn, $order_info->real_pay, bcsub($account_data->now_money, $order_info->real_pay, 2), time(), $coupon_name, 1, $order_info->user_id, 1, $can_back_money_flow))->count();

                if (!$s9 or !$s10) {
                    $conn->rollback();
                    return false;
                } else {
                    //更新账户付款使用码状态为 已审核
                    $this->_getPlayCouponCodeTable()->update(array('check_status' => 2), array('order_sn' => $order_info->order_sn));

                    $account_money = $order_info->real_pay;
                    $real_pay = 0;
                    $trade_no = $adapter->getDriver()->getLastGeneratedValue(); //交易流水记录
                    //$conn->commit();
                }

            }


            //判断是否是团购订单
            $pay_status = 2;
            if ($order_info->group_buy_id != 0) {
                //团购订单
                $group_data = $adapter->query("select * from play_group_buy WHERE  id=?", array($order_info->group_buy_id))->current();
//                $group_data = $this->_getPlayGroupBuyTable()->get(array('id' => $order_info->group_buy_id));
                if (!$group_data) {
                    $conn->rollback();
                    return false;
                }
                if (($group_data->join_number + 1) == $group_data->limit_number) {
                    //团购完成
//                    $this->_getPlayGroupBuyTable()->update(array('join_number' => new Expression('join_number+1'), 'status' => 2), array('id' => $order_info->group_buy_id));
                    $g1 = $adapter->query("update play_group_buy set join_number=join_number+1,status=2 WHERE id=?", array($order_info->group_buy_id))->count();
                    if (!$g1) {
                        $conn->rollback();
                        return false;
                    }
                    //更新其他订单数据
                    //$this->_getPlayOrderInfoTable()->update(array('order_status' => 1, 'pay_status' => $pay_status), array('group_buy_id' => $order_info->group_buy_id, 'pay_status' => 7));
                    $g2 = $adapter->query("update play_order_info set order_status=1,pay_status=? WHERE group_buy_id=? and pay_status=7", array($pay_status, $order_info->group_buy_id))->count();
                    if (!$g2) {
                        $conn->rollback();
                        return false;
                    }
                } else {
                    //团购中
                    $pay_status = 7;
                    //$this->_getPlayGroupBuyTable()->update(array('join_number' => new Expression('join_number+1')), array('id' => $order_info->group_buy_id));
                    $g1 = $adapter->query("update play_group_buy set join_number=join_number+1 WHERE id=?", array($order_info->group_buy_id))->count();
                    if (!$g1) {
                        $conn->rollback();
                        return false;
                    }
                }
            }

            //支付成功
            $approve_status = ($pay_type == 'account') ? 2 : 1;
            $s = $adapter->query("update play_order_info set account_type=?,order_status=?,pay_status=?,trade_no=?,account=?,real_pay=?,account_money=?,approve_status=? WHERE order_sn=?",
                array($pay_type, 1, $pay_status, $trade_no, $uid, $real_pay, $account_money, $approve_status, $order_info->order_sn))->count();

            if (!$s) {
                $conn->rollback();
                return false;
            }
            // 记录操作日志
            $s = $adapter->query('INSERT INTO play_order_action (order_id,play_status,action_user,action_note,dateline,action_user_name) VALUES (?,?,?,?,?,?)', array(
                $order_info->order_sn,
                2,
                $order_info->user_id,
                '支付成功',
                time(),
                '用户' . $order_info->username
            ));

            if (!$s) {
                $conn->rollback();
                return false;
            }
            $conn->commit();  //核心事务完成

            $total_money = bcadd($account_money, $real_pay, 2);  //账户加银行卡总支付金额

            $link_data = $this->_getPlayGameInfoTable()->get(array('id' => $order_info_game->game_info_id));
            $use_time = date('Y-m-d', $link_data->end_time);

            //判断是否 智游宝的
            $zyb_code = Null;
            if ($link_data->goods_sm) {

                $adapter = $this->_getAdapter();
                $code_data_list = $this->_getPlayCouponCodeTable()->fetchAll(array('order_sn' => $order_info->order_sn));

                $Zyb = new ZybPay();
                $result = $Zyb->pay($order_info->order_sn);
                if ($result['code'] == '0' && $result['description'] == '成功') {
                    $zyb_code = $result['orderResponse']['order']['assistCheckNo'];
                    foreach ($code_data_list as $v_code) {
                        $adapter->query('INSERT INTO play_zyb_info (order_sn, zyb_type, code_id, dateline, zyb_code, status, buy_time) VALUES (?,?,?,?,?,?,?)', array(
                            $v_code->order_sn,
                            1,
                            $v_code->id,
                            time(),
                            $zyb_code,
                            2,
                            time()
                        ));
                    }
                    // 更新正确的时间
                    $Zyb->getOrderInfo($order_info->order_sn);

                } else {
                    $adapter->query('INSERT INTO play_zyb_info (order_sn, zyb_type, dateline) VALUES (?,?,?)', array(
                        $order_info->order_sn,
                        2,
                        time(),
                    ));

                    //下单失败 记录下单失败原因
                    $this->_getPlayOrderActionTable()->insert(
                        array('order_id' => $order_info->order_sn,
                            'play_status' => 101, //智游宝 错误
                            'action_user' => 1,
                            'action_note' => "智游宝下单失败原因: ". $result['description'],
                            'dateline' => time(),
                            'action_user_name' => '系统插入',
                        )
                    );

                    //直接退款
                    foreach ($code_data_list as $v_code) {
                        $this->backIng($order_info->order_sn, $v_code->id . $v_code->password, 2);
                    }
                    return true;

                }

            }

            //支付完成消息

            $message = "您购买的{$order_info->coupon_name}已支付完成，请于{$use_time}之前使用。";
            if ($pay_status == 2) {
                if ($order_info->group_buy_id != 0) {
                    // 团购成功 群发短信  后期 异步
                    $order_list = $this->_getPlayOrderInfoTable()->fetchAll(array('group_buy_id' => $order_info->group_buy_id, 'order_status' => 1, 'pay_status' => 2));
                    foreach ($order_list as $o) {
                        $out_trade_no = $o->order_sn;
                        $code_data = $this->_getPlayCouponCodeTable()->fetchAll(array('order_sn' => $out_trade_no));
                        $code_len = '';
                        foreach ($code_data as $v) {
                            if ($code_len) {
                                $code_len .= ',(' . $v->id . $v->password . ')';
                            } else {
                                $code_len .= '(' . $v->id . $v->password . ')';
                            }
                        }

                        if ($zyb_code) {
                            SendMessage::Send13($o->buy_phone, $order_info->coupon_name, $code_data->count(), $zyb_code);
                        } else {
                            SendMessage::Send4($o->buy_phone, $order_info->coupon_name, $total_money, $code_len,$order_info->order_city);
                        }

                    }

                    $this->sendMes($order_info->user_id, $message, json_encode(array('type' => 'group', 'id' => $order_info->coupon_id, 'lid' => (string)$order_info->order_sn, 'group_buy_id' => $order_info->group_buy_id)));
                    // 更新圈子内分享的数量
                    $this->_getMdbSocialCircleMsg()->update(array('object_data.group_buy_id' => (int)$order_info->group_buy_id), array('$set' => array('object_data.join_number' => $group_data->limit_number)), array('multiple' => true));
                    // 跟新消息数量
                    $this->_getMdbsocialChatMsg()->update(array('object_data.group_buy_id' => (int)$order_info->group_buy_id), array('$set' => array('object_data.join_number' => $group_data->limit_number)), array('multiple' => true));


                    //成团奖励
                    $res = $this->_getPlayWelfareTable()->tableGateway->select(function ($select) use ($group_data) {
                        $select->where(array('object_type' => 3, 'object_id' => $group_data->game_id, 'good_info_id' => $group_data->game_info_id));
                        $select->limit(1);
                    })->current();

                    if ($res) {
                        //奖励团长现金券
                        $coupon = new Coupon();
                        $coupon->addCashcoupon($group_data->uid, $res->welfare_link_id, $order_info->order_sn, 5, 0, '团长奖励', $order_info->order_city);
                    }


                } else {
                    //当前订单兑换码
                    $code_data = $this->_getPlayCouponCodeTable()->fetchAll(array('order_sn' => $order_info->order_sn));
                    $code_len = '';
                    foreach ($code_data as $v) {
                        if ($code_len) {
                            $code_len .= ',(' . $v->id . $v->password . ')';
                        } else {
                            $code_len .= '(' . $v->id . $v->password . ')';
                        }
                    }

                    $this->sendMes($order_info->user_id, $message, json_encode(array('type' => (($order_info->order_type == 1) ? 'coupon' : 'game'), 'id' => $order_info->coupon_id, 'lid' => $order_info->order_sn)));


                    if ($zyb_code) {
                        SendMessage::Send13($order_info->buy_phone, $order_info->coupon_name, $code_data->count(), $zyb_code);
                    } else {
                        SendMessage::Send4($order_info->buy_phone, $order_info->coupon_name, $total_money, $code_len,$order_info->order_city);
                    }


                }
            } elseif ($pay_status == 7) {
                SendMessage::Send15($order_info->buy_phone, $order_info->coupon_name,$group_data->limit_number);
                // 我的消息
                $this->sendMes($order_info->user_id, $message, json_encode(array('type' => 'group', 'id' => $order_info->coupon_id, 'lid' => (string)$order_info->order_sn, 'group_buy_id' => $order_info->group_buy_id)));
                // 更新圈子内分享的数量 +1
                $this->_getMdbSocialCircleMsg()->update(array('object_data.group_buy_id' => (int)$order_info->group_buy_id), array('$inc' => array('object_data.join_number' => 1)), array('multiple' => true));
                // 跟新消息数量 +1
                $this->_getMdbsocialChatMsg()->update(array('object_data.group_buy_id' => (int)$order_info->group_buy_id), array('$inc' => array('object_data.join_number' => 1)), array('multiple' => true));

            }

            //支付完成奖励积分和票券
            $integral = new Integral();
            $integral->getIntegralForBuyGood($order_info->user_id, $order_info->order_sn, $total_money, $order_info->order_city, $order_info->coupon_name);
            //奖励现金券
            $coupon = new Coupon();
            $coupon->getCashCouponByBuy($order_info->user_id, $order_info->coupon_id, $order_info_game->game_info_id, $order_info->order_sn, $order_info->order_city, $order_info->coupon_name);
            //返利
            $cash = new Account();
            $cash->getCashByBuy($order_info->user_id, $order_info->coupon_id, $order_info_game->game_info_id, $order_info->order_sn, $order_info->order_city, $order_info->coupon_name);

        } else {
            return false;
        }

        RedCache::del('D:tneedPay:' . $order_info->user_id);
        return true;
    }
}
