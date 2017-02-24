<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/26
 * Time: 09:39
 */
class OrderData extends Manager
{

    public function insertCommodity($data) {
        $now_time      = time();
        $action_result = array(
            'status' => 0,
            'msg'    => '生成订单失败'
        );
        $order_info_vo = new OrderInfoVo();

        // 生成订单数据
        $order_info_vo->coupon_id         = $data['coupon_id'];
        $order_info_vo->order_status      = 1;
        $order_info_vo->pay_status        = $data['pay_status'];
        $order_info_vo->user_id           = $data['uid'];
        $order_info_vo->username          = $data['user_name'];
        $order_info_vo->phone             = $data['user_phone'];
        $order_info_vo->real_pay          = $data['pay_price'];
        $order_info_vo->account_money     = 0;
        $order_info_vo->voucher           = $cash_money;
        $order_info_vo->voucher_id        = $data['cash_coupon_id'];
        $order_info_vo->coupon_unit_price = $data['unit_price'];
        $order_info_vo->coupon_name       = $data['coupon_title'];
        $order_info_vo->shop_name         = $data['shop_name'];
        $order_info_vo->shop_id           = $data['shop_id'];
        $order_info_vo->buy_number        = $data['buy_number'];
        $order_info_vo->use_number        = 0;
        $order_info_vo->back_number       = 0;
        $order_info_vo->account           = 0;
        $order_info_vo->account_type      = $data['account_type'];
        $order_info_vo->buy_name          = $data['buy_name'];
        $order_info_vo->buy_phone         = $data['buy_phone'];
        $order_info_vo->dateline          = $now_time;
        $order_info_vo->use_dateline      = 0;
        $order_info_vo->order_city        = $data['city'];
        $order_info_vo->order_type        = $data['order_type'];
        $order_info_vo->group_buy_id      = $data['group_buy_id'];
        $order_info_vo->buy_address       = $data['buy_address'];
        $order_info_vo->bid               = $data['$game_info']['id'];

        $data_result_orderInfo = $order_info_vo->save();
        $data_order_sn         = Yii::app()->db->lastInsertID;

        if ($data_result_orderInfo && $data_order_sn) {
            // 生成订单操作记录
            $order_action_vo = new OrderActionVo();

            $order_action_vo->order_id         = $data_order_sn;
            $order_action_vo->play_status      = 0;
            $order_action_vo->action_user      = $data['uid'];
            $order_action_vo->action_note      = $now_time;
            $order_action_vo->action_user_name = $data['user_name'];

            $data_result_order_action          = $order_action_vo->save();

            if ($data_result_order_action) {
                // 更新商品订单表中相应商品记录的总共购买数量
                if ($data['real_buy_number']) {
                    $organizer_game_vo           = OrganizerGameVo::model()->find('id = :id', array(':id' => $data['coupon_id']));
                    $organizer_game_vo->buy_num += $data['real_buy_number'];
                    $data_result_organizer_game  = $organizer_game_vo->save();
                } else {
                    $data_result_organizer_game  = true;
                }

                if ($data_result_organizer_game) {
                    // 若为团购，就先只生成一个验证码
                    if ($data['group_buy']) {
                        $coupon_code_count = 1;
                    } else {
                        $coupon_code_count = $data['real_buy_number'];
                    }

                    // 验证码
                    for ($i = 1; $i <= $coupon_code_count; $i++) {
                        $code = sprintf("%03d", $i) . mt_rand(1000, 9999);

                        $coupon_code_vo               = new CouponCodeVo();
                        $coupon_code_vo->order_sn     = $data_order_sn;
                        $coupon_code_vo->sort         = $i;
                        $coupon_code_vo->status       = 0;
                        $coupon_code_vo->password     = $code;
                        $coupon_code_vo->use_store    = 0;
                        $coupon_code_vo->use_datetime = 0;

                        $data_result_coupon_code       = $coupon_code_vo->save();

                        if (!$data_result_coupon_code) {
                            $action_result = array(
                                'status' => 0,
                                'msg'    => '写入验证码失败'
                            );

                            return $action_result;
                        }
                    }

                    // 购买保险
                    $data_result_insure = $this->buyInsure($data['game_info']['insure_num_per_order'], $data_order_sn, $data['coupon_id'], $data['game_info']['insure_days'], $buy_number * ($data['game_info']['insure_num_per_order']), $data['associates_ids']);
                    if ($data_result_insure) {
                        // 生成订单关联数据
                        $order_info_game_vo = new OrderInfoGameVo();

                        $order_info_game_vo->order_sn     = $data_order_sn;
                        $order_info_game_vo->type_name    = $data['game_info']['price_name'];
                        $order_info_game_vo->start_time   = $data['game_info']['start_time'];
                        $order_info_game_vo->end_time     = $data['game_info']['end_time'];
                        $order_info_game_vo->address      = $data['game_info']['shop_name'];
                        $order_info_game_vo->time_id      = $data['game_info']['tid'];
                        $order_info_game_vo->price_id     = $data['game_info']['pid'];
                        $order_info_game_vo->thumb        = $data['organizer_game']['thumb'];
                        $order_info_game_vo->game_info_id = $data['game_info']['id'];
                        $order_info_game_vo->client_id    = $data['client_id'];

                        $data_result_order_info_game      = $order_info_game_vo->save();
                        if ($data_result_order_info_game) {
                            // 生成订单相关的其他附加数据
                            $order_otherdata = new OrderOtherdataVo();
                            $order_otherdata->order_sn = $data_order_sn;
                            $order_otherdata->message  = $data['message'];
                            $order_otherdata->comment  = 0;

                            $data_result_order_other_data= $order_otherdata->save();
                            if ($data_result_order_other_data) {
                                $model_userData = new UserData();
                                // 进行购买资格券的使用
                                $data_param = array(
                                    'uid'               => $data['uid'],
                                    'qualify_coupon_id' => $data['qualify_coupon_id'],
                                    'order_sn'          => $data['order_sn'],
                                    'qualified'         => $data['organizer_game']['qualified'],
                                    'coupon_id'         => $data['coupon_id']
                                );
                                $data_result_use_buy_qualify = $model_userData->useBuyQualify($data_param);

                                // 进行现金券的使用
                                $data_param = array(
                                    'uid'            => $data['uid'],
                                    'pay_price'      => bcmul($data['buy_number'], $data['unit_price'], 2),
                                    'cash_coupon_id' => $data['cash_coupon_id'],
                                );
                                $data_result_use_cash_coupon = $model_userData->useCashCoupon($data_param);

                                // 进行积分的使用
                                $data_param = array(
                                    'uid'       => $data['uid'],
                                    'use_score' => $data['use_score'],
                                    'integral'  => $data['game_info']['integral'],
                                );
                                $data_result_use_for_purchase = $model_userData->useForPurchase($data_param);

                                if ($data_result_use_buy_qualify['status'] && $data_result_use_cash_coupon['status'] && $data_result_use_for_purchase['status']) {
                                    $action_result = array(
                                        'status' => 1,
                                        'msg'    => '生成订单成功',
                                        'data'   => $data_order_sn
                                    );
                                } else {
                                    $action_result = array(
                                        'status' => 0,
                                        'msg'    => '资格券、现金券、积分中至少一个出现问题'
                                    );
                                }
                            }
                        }
                    } else {
                        $action_result = array(
                            'status' => 0,
                            'msg'    => '购买保险失败'
                        );
                    }
                }
            }
        }

        return $action_result;
    }

    public function getCashCouponPrice($uid, $real_pay, $cash_coupon_id)
    {
        $time = time();
        $price = 0;
        if ($cash_coupon_id) {
            $cash_coupon_user_link_vo = CashcouponUserLinkVo::model()->find('
                id = :id and
                pay_time = 0 and
                user_stime < :user_stime and
                user_etime > :user_etime and
                uid = :uid', array(':id' => $cash_coupon_id, ':user_stime' => $time, ':user_etime' => $time, ':uid' => $uid));
            if (!$cash_coupon_user_link_vo) {
                $result['status'] = 0;
                $result['msg'] = '现金券状态异常或已使用!';
                HttpUtil::out($result);
            } elseif ($cash_coupon_user_link_vo->price > $real_pay) {
                $result['status'] = 0;
                $result['msg'] = '现金券金额大于账户金额!';
                HttpUtil::out($result);
            } else {
                $price = $cash_coupon_user_link_vo->price;
            }
        }
        return $price;
    }

    public function getBuyNumberByGroupBuy($data) {
        // 获取购买数量
        if ($data['group_buy'] == 1) {
            // 组团的情况
            $data['real_buy_number'] = $data['organizer_game']['g_limit'];
        } elseif ($data['group_buy'] == 2) {
            // 参加组团
            $data['real_buy_number'] = 0;
        } else {
            $data['real_buy_number'] = $data['buy_number'];
        }
        return $data['real_buy_number'];
    }

    public function getGroupBuyId($data) {
        $data_result = array(
            'status' => 1,
            'msg'    => '操作成功',
            'data'   => $data['data']
        );
        if ($data['group_buy'] == 1) {
            // 新建团
            $group_join_number = 0;                    // 初始化团中成员数量，在支付成功后会加1
            $now_time          = time();               // 当前日期
            $end_time          = $now_time + 3600 * 2; // 组团期限为2个小时

            $vo_group_buy               = new GroupBuyVo();
            $vo_group_buy->status       = 1;
            $vo_group_buy->uid          = $data['uid'];
            $vo_group_buy->add_time     = $now_time;
            $vo_group_buy->end_time     = $end_time;
            $vo_group_buy->join_number  = $group_join_number;
            $vo_group_buy->game_id      = $data['organizer_game']['id'];
            $vo_group_buy->game_info_id = $data['game_info']['id'];
            $vo_group_buy->limit_number = $data['organizer_game']['g_limit'];

            if ($vo_group_buy->save()) {
                // 返回最新插入的id
                $data_result = array(
                    'status' => 1,
                    'msg'    => '操作成功',
                    'data'   => Yii::app()->db->lastInsertID
                );
            } else {
                $data_result = array(
                    'status' => 0,
                    'msg'    => '订单生成失败,团购数据异常-1!',
                );
            }
        } elseif ($data['group_buy'] == 2) {
            // 加入团
            $data_group = GroupBuyVo::model()->find('id = :id', array(':id' => $data['group_buy_id']));
            if (!$data_group) {
                $data_result = array(
                    'status' => 0,
                    'msg'    => '团购数据异常',
                );
            } elseif ($data_group->join_number == $data_group->limit_number) {
                $data_result = array(
                    'status' => 0,
                    'msg'    => '这个团已经满员了哦',
                );
            } else {
                $data_result = array(
                    'status' => 1,
                    'msg'    => '操作成功',
                    'data'   => $data_group['id']
                );
            }
        } else {
            $data_result = array(
                'status' => 1,
                'msg'    => '操作成功',
                'data'   => ''
            );
        }
        return $data_result;
    }

    public function updateGroupBuy($order_info, $pay_status) {
        $res = array();
        //团购订单
        $group_vo = GroupBuyVo::model()->find('id = :id', array(':id' => $order_info->group_buy_id));
        if (!$group_vo) {
            return false;
        }
        $res['group_data'] = $group_vo;
        if (($group_vo->join_number + 1) == $group_vo->limit_number) {
            //团购完成
            $sql = "update play_group_buy
                    set join_number=join_number+1,status=2
                    where id={$order_info->group_buy_id}";
            if (!Yii::app()->db->createCommand($sql)->execute()) {
                return false;
            }
            //更新其他订单数据
            $sql = "update play_order_info
                    set order_status=1, pay_status={$pay_status}
                    WHERE group_buy_id={$order_info->group_buy_id} and pay_status=7";
            if (!Yii::app()->db->createCommand($sql)->execute()) {
                return false;
            }
        } else {
            //团购中
            $pay_status = 7;
            $sql = "update play_group_buy set join_number=join_number+1 WHERE id={$order_info->group_buy_id}";
            if (!Yii::app()->db->createCommand($sql)->execute()) {
                return false;
            }
        }
        $res['pay_status'] = $pay_status;
        return $res;
    }

    public function paySuccess($data, $uid, $user_name, $real_pay, $pay_status, $account_money)
    {
        $now_time = time();
        $sql = "update play_order_info
                set
                account_type={$data['pay_type']},
                order_status=1,
                pay_status={$pay_status},
                trade_no={$data['trade_no']},
                account={$data['account_name']},
                real_pay={$real_pay},
                account_money={$account_money}
                where order_sn={$data['order_sn']}";
        if (!Yii::app()->db->createCommand($sql)->execute()) {
            return false;
        }
        // 记录操作日志
        $sql = "insert into play_order_action (order_id,play_status,action_user,action_note,dateline,action_user_name)
                values (
                {$data['order_sn']},
                2,
                {$uid},
                '支付成功',
                {$now_time},
                '用户' . {$user_name})";
        if (!Yii::app()->db->createCommand($sql)->execute()) {
            return false;
        }
        return true;
    }

    public function postZyb($order_sn)
    {
        $code_data_list = CouponCodeVo::model()->findAll(array('order_sn' => $order_sn));

        // todo 这里需要改用接口的方式操作
        $Zyb = new ZybService();
        $result = $Zyb->pay($order_sn);

        if ($result['code'] == '0' && $result['description'] == '成功') {
            $zyb_code = $result['orderResponse']['order']['assistCheckNo'];
            foreach ($code_data_list as $v_code) {
                $zyb_info_vo = new ZybInfoVo();
                $zyb_info_vo->order_sn = $v_code->order_sn;
                $zyb_info_vo->zyb_type = 1;
                $zyb_info_vo->code_id = $v_code->id;
                $zyb_info_vo->dateline = time();
                $zyb_info_vo->zyb_code = $zyb_code;
                $zyb_info_vo->status = 2;
                $zyb_info_vo->buy_time = time();
                $zyb_info_vo->save();
            }

            // todo 调用接口
            $Zyb->getOrderInfo($order_sn);
            return $zyb_code;
        } else {
            $zyb_info_vo = new ZybInfoVo();
            $zyb_info_vo->order_sn = $order_sn;
            $zyb_info_vo->zyb_type = 2;
            $zyb_info_vo->dateline = time();
            $zyb_info_vo->save();
            //直接退款
            foreach ($code_data_list as $v_code) {
                // todo 退款 调用退款接口
                $service = new OrderService();
                $service->activityBack($order_sn, $v_code->id . $v_code->password, 2);
            }
            return null;
        }
    }



    public function getRefundTime($order_type, $coupon_id)
    {
        if ($order_type == 1) {
            $sql = "select * from play_coupons where coupon_id = {$coupon_id}";
        } else {
            $sql = "select * from play_organizer_game where id = {$coupon_id}";
        }
        $res = Yii::app()->db->createCommand($sql)->queryRow();
        if (!$res->refund_time) {
            $result['status'] = 0;
            $result['msg'] = '允许退款时间未设置';
            HttpUtil::out($result);
        }
        return $res->refund_time;
    }

    public function getZybData($order_sn, $id)
    {
        $sql = "select * from play_zyb_info where order_sn={$order_sn} and zyb_type=1 and code_id={$id}";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function updateZybInfo($id, $order_sn, $back_number)
    {
        $sql = "update play_zyb_info set status = 3, back_number = {$back_number} where order_sn = {$order_sn} and code_id = {$id}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateCouponCode($id, $order_sn, $money)
    {
        $sql = "update play_coupon_code set status = 3, back_time = {time()}, back_money = {$money} where order_sn = {$order_sn} and id = {$id} and status = 0";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateCashcouponUserLink($voucher_id, $back_money, $type = 0)
    {
        $sql = "update play_cashcoupon_user_link set is_back = 1,";
        if ($type == 0) {
            $sql .= "back_money = {$back_money}";
        } elseif ($type == 1) {
            $sql .= "back_money = back_money+{$back_money}";
        }
        $sql .= " where id = {$voucher_id}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateCouponCodeStatus($order_sn)
    {
        $sql = "update play_coupon_code set status = 3 where order_sn = {$order_sn}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateCoupons($coupon_id)
    {
        $sql = "update play_coupons set coupon_buy = coupon_buy - 1 where coupon_id = {$coupon_id}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateGameInfoBuy($game_info_id)
    {
        $sql = "update play_game_info set buy = buy - 1 where id = {$game_info_id}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateOrganizerGameBuyNum($coupon_id)
    {
        $sql = "update play_organizer_game set buy_num = buy_num - 1 where id = {$coupon_id}";
        return Yii::app()->db->createCommand($sql)->execute();
    }

    public function updateGroupByJoinNumber($group_buy_id)
    {
        $sql = "update play_group_buy set join_number = join_number - 1 where id = {$group_buy_id}";
        return Yii::app()->db->createCommand($sql)->execute();
    }


    public function insertActivity($data, $real_pay, $cash_money, $buy_number, $total_price)
    {
        $time = time();

        $transaction = Yii::app()->db->beginTransaction();

        // 剩余量
        $all_buy_number = $data['all_buy_number'];
        $surplus = $data['event_data']['most_number'] - $all_buy_number;

        $excercise_event_vo = ExerciseEventVo::model()->find('id = :id', array(':id' => $data['event_data']['id']));
        if ($excercise_event_vo->join_number <= $surplus) {
            $excercise_event_vo->join_number += $all_buy_number;
            $excercise_event_vo->save();
        } else {
            $transaction->rollback();
            $result['status'] = 0;
            $result['msg'] = '数量不够了!';
            HttpUtil::out($result);
        }

        if ($excercise_event_vo->join_number >= $excercise_event_vo->perfect_number) {
            $excercise_event_vo->sell_status = 2;
            $excercise_event_vo->save();
        }

        // UPDATE play_excercise_price

        foreach ($data['data']['charges'] as $v) {
            $excercise_price_vo = ExercisePriceVo::model()->find('id = :id', array(':id' => $v['id']));
            $excercise_price_vo->buy_number += $v['buy_number'];
            $res = $excercise_price_vo->save();
            if (!$res) {
                $transaction->rollback();
                $result['status'] = 0;
                $result['msg'] = '内部错误!';
                HttpUtil::out($result);
            }
        }

        // 主表记录插入
        $data_arr_order_info = array(
            'coupon_id'         => $data['event_data']['id'],
            'order_status'      => 1,
            'pay_status'        => 0,
            'user_id'           => $data['user_data']['uid'],
            'username'          => $data['user_data']['username'],
            'phone'             => $data['user_data']['phone'],
            'real_pay'          => $real_pay,  //银行卡需要支付的金额
            'account_money'     => 0,  //用户账户需要支付的金额
            'voucher'           => $cash_money,//现金券金额
            'voucher_id'        => $data['cash_coupon_id'],
            'coupon_unit_price' => 0,
            'coupon_name'       => $data['base_data']['name'],
            'shop_name'         => $data['event_data']['shop_name'],
            'shop_id'           => $data['event_data']['shop_id'],
            'buy_number'        => $buy_number,
            'use_number'        => 0,
            'back_number'       => 0,
            'account'           => '',
            'account_type'      => 'nopay',
            'buy_name'          => $data['buy_name'],
            'buy_phone'         => $data['buy_phone'],
            'dateline'          => $time,
            'use_dateline'      => 0,
            'order_city'        => $data['city'],
            'order_type'        => 3,
            'group_buy_id'      => 0,
            'buy_address'       => $data['buy_address'],
            'bid'               => $data['base_data']['id'],
            'total_price'       => $total_price
        );
        $order_info_insert = new OrderInfoVo();
        $order_sn = $order_info_insert->commonSave($data_arr_order_info);

        if (!$order_sn) {
            $transaction->rollback();
            $result['status'] = 0;
            $result['msg'] = '生成订单失败!';
            HttpUtil::out($result);
        }

        $data_arr_order_action = array(
            'order_id'         => $order_sn,
            'play_statu'       => 0,
            'action_user'      => $data['user_data']['uid'],
            'action_note'      => '下单成功',
            'dateline'         => $time,
            'action_user_name' => '用户' . $data['user_data']['username']);
        $order_action = new OrderInfoVo();
        $res = $order_action->commonSave($data_arr_order_action);

        if (!$res) {
            $transaction->rollback();
            $result['status'] = 0;
            $result['msg'] = '插入订单记录失败!';
            HttpUtil::out($result);
        }

        // 生成卡券密码
        $n = 0;
        $ins_code[] = array();

        foreach ($data['charges'] as $k => $v) {
            for ($i = $v['buy_number']; $i > 0; $i--) {
                //验证码
                $code = $order_sn . sprintf("%02d", $n) . mt_rand(10, 99);  //验证码
                $data_arr_codes = array(
                    'eid'          => $data['event_data']['id'],
                    'order_sn'     => $order_sn,
                    'code'         => $code,
                    'uid'          => $data['user_data']['uid'],
                    'back_time'    => 0,
                    'back_money'   => 0,
                    'status'       => 0,
                    'use_dateline' => 0,
                    'pid'          => $v['id'],
                    'dateline'     => $time,
                    'price'        => $v['price']
                );
                $exe_code_vo = new ExerciseCodeVo();
                $res = $exe_code_vo->commonSave($data_arr_codes);
                if (!$res) {
                    $transaction->rollback();
                    $result['status'] = 0;
                    $result['msg'] = '生成验证码失败!';
                    HttpUtil::out($result);
                }

                if ($v['is_other'] != 1) {
                    $ins_code[] = $code;
                }
                $n++;
            }
        }

        // 见面地点
        if ($data['meeting_id']) {
            $meeting_data = ExerciseMeetingVo::model()->find('id = :id', array(':id' => $data['meeting_id']));
            $meeting_place = $meeting_data->meeting_place;
            $meeting_time = $meeting_data->meeting_time;
        } else {
            $meeting_place = '';
            $meeting_time = 0;
        }

        if (count($ins_code) == count($data['associates_ids'])) {
            $full_sssociates = 1;
        } else {
            $full_sssociates = 0;

        }

        $share_order_sn = $data['share_order_sn'] > 0 ? $data['share_order_sn'] : 0;

        // 写入order_otherdata
        $data_arr_order_otherdata = array(
            'rder_sn'         => $order_sn,
            'message'         => $data['message'],
            'comment'         => 0,
            'meeting_place'   => $meeting_place,
            'meeting_time'    => $meeting_time,
            'meeting_id'      => $data['meeting_id'],
            'full_sssociates' => $full_sssociates,
            'share_order_sn'  => $share_order_sn
        );
        $order_otherdata_vo = new OrderOtherdataVo;
        $res = $order_otherdata_vo->commonSave($data_arr_order_otherdata);
        if (!$res) {
            $transaction->rollback();
            $result['status'] = 0;
            $result['msg'] = '插入订单关联数据失败!';
            HttpUtil::out($result);
        }

        // 购买保险
        $res = $this->buyInsure(count($ins_code), $order_sn, $data['event_data']['id'], $data['data_event']['insurance_id'], count($ins_code) - count($data['associates_ids']), $data['associates_ids']);
        if (!$res) {
            $transaction->rollback();
            $result['status'] = 0;
            $result['msg'] = '购买保险失败!';
            HttpUtil::out($result);
        }

        // 使用现金券
        if ($data['cash_coupon_id']) {
            $cash_coupon_user_link_vo = CashcouponUserLinkVo::model()->find('
                id = :id and
                pay_time = 0 and
                user_stime < :user_stime and
                user_etime > :user_etime and
                uid = :uid', array(':id' => $data['cash_coupon_id'], ':user_stime' => $time, ':user_etime' => $time, ':uid' => $data['user_data']['uid']));
            $data_arr_cash_coupon = array(
                'pay_time'      => $time,
                'use_order_id'  => $order_sn,
                'use_object_id' => $data['event_data']['id'],
                'use_type'      => 2
            );
            $res = $cash_coupon_user_link_vo->commonSave($data_arr_cash_coupon, $cash_coupon_user_link_vo);

            if (!$res) {
                $transaction->rollback();
                $result['status'] = 0;
                $result['msg'] = '现金券使用失败,已使用或已过期!';
                HttpUtil::out($result);
            }
            $transaction->commit();
            $result = array(
                'stauts'   => true,
                'order_sn' => $order_sn
            );
            return $result;
        }
        return false;
    }

    //支付完成的消息
    public function sendMes($uid, $message, $link_id) {
        $action_result = array(
            'status' => 1,
            'msg'    => '通知消息已发送'
        );
        if (empty($uid)) {
            $action_result = array(
                'status' => 0,
                'msg'    => '通知消息发送失败，缺少用户uid'
            );
        } else {
            $pdo = Yii::app()->db;
            $sql = "INSERT INTO play_user_message_table (uid, type, title, deadline, message, link_id) VALUES (:uid, 5, '支付完成', :time, :message, :link_id)";
            $command = $pdo->createCommand($sql);

            $command->bindParam(":uid",     $uid);
            $command->bindParam(":time",    time());
            $command->bindParam(":message", $message);
            $command->bindParam(":link_id", $link_id);

            $data_return = $command->execute();

            if (empty($data_return)) {
                $action_result = array(
                    'status' => 0,
                    'msg'    => '通知消息发送失败'
                );
            } else {
                $action_result = array(
                    'status' => 1,
                    'msg'    => '通知消息已发送'
                );
            }
        }

        return $action_result;
    }

    public function checkPayBackWaiting($order_sn)
    {
        $coupon_code_list_vo = CouponCodeVo::model()->findAll('order_sn = :order_sn and status = 3', array(':order_sn' => $order_sn));
        $order_info_vo = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
        if ($coupon_code_list_vo) {
            $order_info_vo->back_number += 1;
            $res = $order_info_vo->save();
        } else {
            $order_info_vo->pay_status = 4;
            $order_info_vo->back_number += 1;
            $res = $order_info_vo->save();
        }
        return $res;
    }

    public function buyInsure($flag, $order_sn, $coupon_id, $product_code, $num, $associates_ids)
    {
        $res = true;
        // 保险
        if ($flag) {
            // 需要购买保险
            $transaction = Yii::app()->db->beginTransaction();

            for ($i = 0; $i < count($associates_ids); $i++) {
                $associates_info = UserAssociatesVo::model()->find('associates_id = :associates_id', array(':associates_id' => $associates_ids[$i]));

                $order_insure_vo = new OrderInsureVo();
                $order_insure_vo->order_sn = $order_sn;
                $order_insure_vo->coupon_id = $coupon_id;
                $order_insure_vo->name = $associates_info->name;
                $order_insure_vo->sex = $associates_info->sex;
                $order_insure_vo->birth = $associates_info->birth;
                $order_insure_vo->id_num = $associates_info->id_num;
                $order_insure_vo->insure_company_id = 1;
                $order_insure_vo->insure_sn = '';
                $order_insure_vo->baoyou_sn = '';
                $order_insure_vo->insure_status = 1;
                $order_insure_vo->associates_id = $associates_ids[$i];
                $order_insure_vo->product_code = $product_code;

                $res = $order_insure_vo->save();
                if (!$res) {
                    $transaction->rollback();
                    return false;
                }

            }

            if (count($associates_ids) < $num) {

                for ($i = 0; $i < $num - count($associates_ids); ++$i) {

                    $order_insure_vo = new OrderInsureVo();
                    $order_insure_vo->order_sn = $order_sn;
                    $order_insure_vo->coupon_id = $coupon_id;
                    $order_insure_vo->name = '';
                    $order_insure_vo->sex = '';
                    $order_insure_vo->birth = '';
                    $order_insure_vo->id_num = '';
                    $order_insure_vo->insure_company_id = 1;
                    $order_insure_vo->insure_sn = '';
                    $order_insure_vo->baoyou_sn = '';
                    $order_insure_vo->insure_status = 0;
                    $order_insure_vo->associates_id = 0;
                    $order_insure_vo->product_code = $product_code;

                    $res = $order_insure_vo->save();
                    if (!$res) {
                        $transaction->rollback();
                        return false;
                    }
                }
            }
            $transaction->commit();
        }
        return $res;
    }

    public function sendMessageAfterInviteSuccess($share_order_sn, $order_sn, $city, $uid)
    {
        if (!$share_order_sn) {
            return false;
        }

        $invite = new InviteService();
        $account = new AccountService();
        $order_info_vo = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $share_order_sn));
        $to_uid = $order_info_vo->user_id;
        if ($order_info_vo) {
            $money = $invite->getRebate($order_info_vo);
            if ($money) {
                $res = $account->recharge($to_uid,$money,0,'好友购买'. $order_info_vo->coupon_name .'获得返利'.$money.'元', 16, $order_sn, false, 0, $city);
                if ($res) {
                    $invite->WeiXinMsg($uid, $to_uid, $money, 'buy', $order_sn);
                    $invite->GetuiMsg($uid, $to_uid, $money, 'buy');
                    return true;
                }
            }
        }
        return false;
    }

    public static function getOrderInfoByOrderSn ($order_sn) {
        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => array()
        );
        if (empty($order_sn)) {
            $data_result = array(
                'status' => 0,
                'msg'    => '订单的编号缺失'
            );
        } else {
            $data_order_info = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn))->getAttributes();
            if (empty($data_order_info)) {
                $data_result = array(
                    'status' => 0,
                    'msg'    => '订单信息未找到'
                );
            } else {
                $data_result = array(
                    'status' => 1,
                    'msg'    => '查询成功',
                    'data'   => $data_order_info
                );
            }
        }
        return $data_result;
    }

    public static function getOrderInfoGameByOrderSn ($order_sn) {
        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => array()
        );
        if (empty($order_sn)) {
            $data_result = array(
                'status' => 0,
                'msg'    => '订单的编号缺失'
            );
        } else {
            $data_order_info_game = OrderInfoGameVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn))->getAttributes();
            if (empty($data_order_info_game)) {
                $data_result = array(
                    'status' => 0,
                    'msg'    => '订单场次信息未找到'
                );
            } else {
                $data_result = array(
                    'status' => 1,
                    'msg'    => '查询成功',
                    'data'   => $data_order_info_game
                );
            }
        }
        return $data_result;
    }
}
