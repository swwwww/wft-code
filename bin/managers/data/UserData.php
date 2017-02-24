<?php

/**
 * 用户数据层
 *
 * @classname: UserData
 * @author   11942518@qq.com | quenteen
 * @date     2016-7-2
 */
class UserData extends Manager
{

    //根据用户id获取用户信息
    public static function getUserById($user_id)
    {
        $ps_user_vo = UserVo::model()->find('uid = :uid', array(
            ':uid' => $user_id,
        ));

        if ($ps_user_vo) {
            $wechat_user = WechatUserVo::model()->find("uid = :uid and appid = :appid and login_type = 'weixin_wap' order by id desc", array(
                ':uid' => $user_id,
                ':appid' => Yii::app()->params['wechat']['appid'],
            ));

            $ps_user_vo = $ps_user_vo->getAttributes();
            if ($wechat_user) {
                $wechat_user = $wechat_user->getAttributes();
                $ps_user_vo['wechat_user'] = $wechat_user;
            }

            return $ps_user_vo;
        } else {
            return false;
        }
    }

    public static function getBusinessUserById($user_id)
    {
        $user_vo = BusinessUserVo::model()->findByPk($user_id);

        if($user_vo){
            $user_vo = $user_vo->getAttributes();

            $user_vo['organizer_id'] = $user_id;
        }

        return $user_vo;
    }

    /**
     * 根据手机号获取用户
     *
     * @param
     *
     * @return
     * @author 11942518@qq.com | quenteen
     * @date   2016-7-16 下午06:04:20
     */
    public static function getUserByPhone($phone)
    {
        $user_vo = UserVo::model()->find('phone = :phone order by uid desc', array(':phone' => $phone));

        return $user_vo;
    }

    /**
     * 检查用户积分是足够本次消费
     *
     * @param   uid         用户的uid
     * @param   use_score   是否需要积分消费
     * @param   integral    要消费的积分
     *
     * @return
     */
    public function checkIntegralUser($param)
    {
        LogUtil::info('[UserData](checkIntegralUser)[start] with user uid: ' . $param['uid']);
        $check_result = array(
            "status" => 1,
            "msg"    => "检查通过"
        );

        if (!empty($param['use_score'])) {
            if (empty($param['uid']) || empty($param['integral'])) {
                LogUtil::info("在进行积分检查的过程中，发现参数缺失");
                $check_result = array(
                    "status" => 0,
                    "msg"    => "在进行积分检查的过程中，参数缺失"
                );
            } else {
                $pdo = Yii::app()->db;
                $sql = "SELECT * FROM play_integral_user WHERE uid = :uid AND total>=:integral";
                $sql_param = array(
                    "uid"      => $param['uid'],
                    "integral" => $param['game_info']['integral']
                );

                if ($pdo->createCommand($sql)->query($sql_param)) {
                    LogUtil::info("积分不足");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "积分不足"
                    );
                }
            }
        }

        LogUtil::info('[UserData](checkIntegralUser)[end] with user uid: ' . $param['uid']);
        return $check_result;
    }

    /**
     * 进行用户积分的消费
     *
     * @param   uid         用户的uid
     * @param   city        所在城市
     * @param   coupon_name 商品名称
     * @param   coupon_id   商品的id
     * @param   use_score   是否需要积分消费
     * @param   integral    要消费的积分
     *
     * @return
     */
    public function useForPurchase($param)
    {
        LogUtil::info('[UserData](useForPurchase)[start] with user uid: ' . $param['uid']);
        $time = time();
        $action_result = array(
            "status" => 1,
            "msg"    => "执行成功"
        );

        if (!empty($param['use_score'])) {
            if (!empty($param['uid']) && !empty($param['integral']) && !empty($param['coupon_id'])) {
                $pdo = Yii::app()->db;
                $sql = "UPDATE play_integral_user SET total=total-:integral WHERE uid=:uid AND total>=:integral";
                $command = $pdo->createCommand($sql);

                // 绑定参数
                $command->bindParam(":uid", $param['uid']);
                $command->bindParam(":integral", $param['integral']);

                $data_return = $command->execute();
                if (empty($data_return)) {
                    LogUtil::info("积分不足");
                    $action_result = array(
                        "status" => 0,
                        "msg"    => "积分不足"
                    );
                    return $action_result;
                }

                unset($command);

                //积分记录表添加记录
                $sql = "INSERT INTO play_integral (id, uid, `type`, total_score, base_score, award_score, object_id, create_time, city, `desc`)" .
                    " VALUES (NULL, :uid, 102, :score, :score, 1, :object_id, :time, :city, :desc)";

                $command = $pdo->createCommand($sql);

                $data_desc = '购买' . ($param['coupon_name'] ?: '商品') . '消耗积分';
                // 绑定参数
                $command->bindParam(":uid", $param['uid']);
                $command->bindParam(":city", $param['city']);
                $command->bindParam(":time", $time);
                $command->bindParam(":desc", $data_desc);
                $command->bindParam(":score", $param['integral']);
                $command->bindParam(":object_id", $param['coupon_id']);

                $data_return = $command->execute();
                if (empty($data_return)) {
                    LogUtil::info("积分表写入失败");
                    $action_result = array(
                        "status" => 0,
                        "msg"    => "积分表写入失败"
                    );
                }
            } else {
                LogUtil::info("在进行积分更新的过程中，发现参数缺失");
                $action_result = array(
                    "status" => 0,
                    "msg"    => "在进行积分更新的过程中，发现参数缺失"
                );
            }
        }

        LogUtil::info('[UserData](useForPurchase)[end] with user uid: ' . $param['uid']);
        return $action_result;
    }

    /**
     * 检查现金券是否可用，并获取用代金券的票面金额
     *
     * @param   uid             用户的uid
     * @param   pay_price       待支付金额
     * @param   cash_coupon_id  现金券的id
     *
     * @return
     */
    public function checkCashCoupon($param)
    {
        LogUtil::info('[UserData](checkCashCoupon)[start] with user uid: ' . $param['uid']);
        $check_result = array(
            "status" => 1,
            "msg"    => "检查通过",
            "price"  => 0
        );

        if (!empty($param['cash_coupon_id'])) {
            if ((!empty($param['pay_price']) || $param['pay_price'] === 0) && !empty($param['uid'])) {
                // 获取当前系统时间
                $time = time();
                $pdo = Yii::app()->db;
                $sql = "SELECT * FROM play_cashcoupon_user_link WHERE id = :id AND uid = :uid";
                $sql_param = array(
                    'id'  => $param['cash_coupon_id'],
                    'uid' => $param['uid']
                );

                $data_cashcoupon_user_link = $pdo->createCommand($sql)->query($sql_param);

                if (empty($data_cashcoupon_user_link)) {
                    LogUtil::info("现金券信息不存在");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "现金券信息不存在",
                    );
                } elseif ($data_cashcoupon_user_link->price > $param['pay_price']) {
                    LogUtil::info("现金券金额大于要支付金额");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "现金券金额大于要支付金额",
                    );
                } elseif ($data_cashcoupon_user_link->pay_time != 0 || !empty($data_cashcoupon_user_link->use_order_id) || !empty($data_cashcoupon_user_link->use_object_id)) {
                    LogUtil::info("现金券已被使用");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "现金券已被使用",
                    );
                } elseif ($data_cashcoupon_user_link->use_stime > $time) {
                    LogUtil::info("这张现金券还不能使用");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "这张现金券还不能使用",
                    );
                } elseif ($data_cashcoupon_user_link->use_etime < $time) {
                    LogUtil::info("这张现金券已过期");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "这张现金券已过期",
                    );
                } else {
                    $check_result = array(
                        "status" => 1,
                        "msg"    => "检查通过",
                        "price"  => $data_cashcoupon_user_link->price
                    );
                }
            } else {
                LogUtil::info("在进行现金券消费验证的过程中，发现参数缺失");
                $check_result = array(
                    "status" => 0,
                    "msg"    => "在进行现金券消费验证的过程中，发现参数缺失"
                );
            }
        }

        LogUtil::info('[UserData](checkCashCoupon)[end] with user uid: ' . $param['uid']);
        return $check_result;
    }

    /**
     * 进行现金券使用
     *
     * @param   uid             用户的uid
     * @param   cash_coupon_id  现金券的id
     * @param   order_sn        订单编号
     * @param   use_type        现金券消费项目类别
     * @param   coupon_id       现金券消费商品id
     *
     * @return
     */
    public function useCashCoupon($param)
    {
        LogUtil::info('[UserData](useCashCoupon)[start] with user uid: ' . $param['uid']);
        $time = time();
        $action_result = array(
            "status" => 1,
            "msg"    => "执行成功"
        );

        if (!empty($param['cash_coupon_id'])) {
            if (!empty($param['uid']) && !empty($param['order_sn']) && !empty($param['coupon_id'])) {
                $pdo = Yii::app()->db;
                $sql = "UPDATE play_cashcoupon_user_link SET pay_time = :time, use_order_id = :order_id, use_object_id = :object_id, use_type = :use_type WHERE id = :id AND use_stime =< :time AND use_etime >= :time AND uid = :uid";
                $command = $pdo->createCommand($sql);

                // 绑定参数
                $command->bindParam(":id", $param['cash_coupon_id']);
                $command->bindParam(":uid", $param['uid']);
                $command->bindParam(":time", $time);
                $command->bindParam(":order_id", $param['order_sn']);
                $command->bindParam(":use_type", $param['use_type'] ? $param['use_type'] : 1);
                $command->bindParam(":object_id", $param['coupon_id']);

                $data_return = $command->execute();
                if (empty($data_return)) {
                    LogUtil::info("现金券使用失败 已使用或已过期");
                    $action_result = array(
                        "status" => 0,
                        "msg"    => "现金券使用失败 已使用或已过期"
                    );
                }
            } else {
                LogUtil::info("在进行现金券消费的过程中，发现参数缺失");
                $action_result = array(
                    "status" => 0,
                    "msg"    => "在进行现金券消费的过程中，发现参数缺失"
                );
            }
        }

        LogUtil::info('[UserData](useCashCoupon)[end] with user uid: ' . $param['uid']);
        return $action_result;
    }

    /**
     * 进行购买资格验证
     *
     * @param   uid                用户的uid
     * @param   qualify_coupon_id  资格券的id
     * @param   order_sn           订单编号
     * @param   qualified          商品是否需要购买资格
     * @param   coupon_id          资格券对应的商品id
     *
     * @return
     */
    public function checkBuyQualify($param)
    {
        LogUtil::info('[UserData](checkBuyQualify)[start] with user uid: ' . $param['uid']);
        $check_result = array(
            "status" => 1,
            "msg"    => "检查通过",
        );

        if ($param['qualified'] == 2) {
            if (!empty($param['qualify_coupon_id']) && !empty($param['uid'])) {
                $pdo = Yii::app()->db;
                $sql = "SELECT * FROM play_qualify_coupon WHERE id = :id AND uid = :uid AND status = 1";
                $sql_param = array(
                    'id'  => $param['qualify_coupon_id'],
                    'uid' => $param['uid']
                );
                $data_qualify_coupon = $pdo->createCommand($sql)->query($sql_param);

                if (empty($data_qualify_coupon)) {
                    LogUtil::info("资格券信息缺失");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "资格券信息缺失",
                    );
                } elseif (true){//(!empty($data_qualify_coupon->pay_time) || !empty($data_qualify_coupon->use_order_id) || !empty($data_qualify_coupon->)) {
                    LogUtil::info("资格券已被使用");
                    $check_result = array(
                        "status" => 0,
                        "msg"    => "资格券已被使用",
                    );
                }
            } else {
                LogUtil::info("在进行资格券验证的过程中，发现参数缺失");
                $check_result = array(
                    "status" => 0,
                    "msg"    => "在进行资格券验证的过程中，发现参数缺失"
                );
            }
        }

        LogUtil::info('[UserData](checkBuyQualify)[end] with user uid: ' . $param['uid']);
        return $check_result;
    }

    /**
     * 进行购买资格券使用
     *
     * @param   uid                用户的uid
     * @param   qualify_coupon_id  资格券的id
     * @param   order_sn           订单编号
     * @param   qualified          商品是否需要购买资格
     * @param   coupon_id          资格券对应消费商品id
     *
     * @return
     */
    public function useBuyQualify($param)
    {
        LogUtil::info('[UserData](useBuyQualify)[start] with user uid: ' . $param['uid']);
        $time = time();
        $action_result = array(
            "status" => 1,
            "msg"    => "执行成功"
        );

        if ($param['qualified'] == 2) {
            if (!empty($param['qualify_coupon_id']) && !empty($param['uid']) && !empty($param['order_sn']) && !empty($param['coupon_id'])) {
                $pdo = Yii::app()->db;
                $sql = "UPDATE play_qualify_coupon SET pay_time = :time, use_order_id = :order_id, pay_object_id = :object_id WHERE uid=:uid AND id=:id";
                $command = $pdo->createCommand($sql);

                // 绑定参数
                $command->bindParam(":id", $param['qualify_coupon_id']);
                $command->bindParam(":uid", $param['uid']);
                $command->bindParam(":time", $time);
                $command->bindParam(":order_id", $param['order_sn']);
                $command->bindParam(":object_id", $param['coupon_id']);

                $data_return = $command->execute();
                if (empty($data_return)) {
                    LogUtil::info("资格券使用失败 已使用或已过期");
                    $action_result = array(
                        "status" => 0,
                        "msg"    => "资格券使用失败 已使用或已过期"
                    );
                }
            } else {
                LogUtil::info("在进行资格券使用的过程中，发现参数缺失");
                $action_result = array(
                    "status" => 0,
                    "msg"    => "在进行资格券使用的过程中，发现参数缺失"
                );
            }
        }

        LogUtil::info('[UserData](useBuyQualify)[end] with user uid: ' . $param['uid']);
        return $action_result;
    }

    /**
     * 根据uid获取用户的余额信息
     *
     * @param   uid                用户的uid
     *
     * @return
     */
    public function getUserAccountByUid($param)
    {
        LogUtil::info('[UserData](getUserAccountByUid)[start] with user uid: ' . $param['uid']);
        $time = time();
        $data_result = array(
            "status" => 1,
            "msg"    => "执行成功"
        );

        if (empty($param['uid'])) {
            $data_result = array(
                "status" => 0,
                "msg"    => "用户信息缺失，账户余额获取失败"
            );
        } else {
            $data_return = AccountVo::model()->find('uid = :uid', array(':uid' => $param['uid']))->getAttributes();

            if (empty($data_return)) {
                $data_result = array(
                    "status" => 0,
                    "msg"    => "用户账户余额信息获取失败"
                );
            } else {
                $data_result = array(
                    "status" => 1,
                    "msg"    => "用户信息缺失，账户余额获取失败",
                    "data"   => $data_return
                );
            }
        }

        LogUtil::info('[UserData](getUserAccountByUid)[end] with user uid: ' . $param['uid']);
        return $data_result;
    }

    /**
     * 更新账户余额信息并记录日志
     *
     * @param   uid           用户的uid
     * @param   account_money 用户余额
     * @param   order_sn      订单编号
     * @param   order_info    订单信息
     *
     * @return
     */
    public function updateUserAccount($param)
    {
        LogUtil::info('[UserData](updateUserAccount)[start] with user uid: ' . $param['uid']);
        $time = time();
        $action_result = array(
            'status' => 1,
            'msg'    => "执行成功"
        );

        if (empty($param['uid'])) {
            LogUtil::info('[UserData](updateUserAccount) 用户信息缺失，账户余额更新失败');
            $action_result = array(
                'status' => 0,
                'msg'    => "用户信息缺失，账户余额更新失败"
            );
        } else {
            $pdo = Yii::app()->db;
            $data_account = $param['account_data'];
            $data_order_info = $param['order_info'];

            if (empty($data_account)) {
                $data_account = $this->getUserAccountByUid($param);
            }

            if (empty($data_order_info)) {
                $data_order_info = OrderData::getOrderInfoByOrderSn($param['order_sn']);
            }

            if (empty($data_account) || empty($data_order_info)) {
                LogUtil::info('[UserData](updateUserAccount) 用户信息更新过程中缺乏必要信息');
                $action_result = array(
                    'status' => 0,
                    'msg'    => "用户信息更新过程中缺乏必要信息"
                );
                return $action_result;
            }

            if (($data_account->now_money - $data_order_info->real_pay) < $data_account->can_back_money) {
                $sql = "UPDATE play_account SET now_money = now_money - :pay_price, can_back_money = now_money, last_time = :time WHERE uid = :uid AND now_money >= :pay_price";
                $can_back_money_flow = $data_order_info->real_pay - ($data_account->now_money - $data_account->can_back_money);
            } else {
                $sql = "UPDATE play_account SET now_money=now_money-:pay_price, last_time=:time WHERE uid=:uid AND now_money >= :pay_price";
                $can_back_money_flow = null;
            }

            $command = $pdo->createCommand($sql);

            // 绑定参数
            $command->bindParam(":uid", $param['uid']);
            $command->bindParam(":time", $time);
            $command->bindParam(":pay_price", $data_order_info->real_pay);

            $data_return = $command->execute();

            if (empty($data_return)) {
                $action_result = array(
                    'status' => 0,
                    'msg'    => "用户账户余额信息更新失败"
                );
            } else {
                // 记录用户账户操作日志
                $data_coupon_name = '购买商品' . $data_order_info['coupon_name'] ?: '';

                // 保存成功之后写log
                $vo_account_log = new AccountLogVo();
                $vo_account_log->uid = $data_order_info['user_id'];
                $vo_account_log->status = 1;
                $vo_account_log->dateline = $time;
                $vo_account_log->object_id = $data_order_info['order_sn'];
                $vo_account_log->flow_money = $data_order_info['real_pay'];
                $vo_account_log->action_type = 2;
                $vo_account_log->description = $data_coupon_name;
                $vo_account_log->user_account = $data_order_info['user_id'];
                $vo_account_log->check_status = 1;
                $vo_account_log->surplus_money = bcsub($data_account['now_money'], $data_order_info['real_pay'], 2);
                $vo_account_log->action_type_id = 1;
                $vo_account_log->can_back_money_flow = $can_back_money_flow;
                $data_return_account_log = $vo_account_log->save();

                if (empty($data_return_account_log)) {
                    $action_result = array(
                        'status' => 0,
                        'msg'    => "用户信息缺失，账户余额获取失败",
                    );
                } else {
                    $action_result = array(
                        'status' => 1,
                        'msg'    => "更新成功",
                        'data'   => Yii::app()->db->lastInsertID
                    );
                }
            }
        }

        LogUtil::info('[UserData](updateUserAccount)[end] with user uid: ' . $param['uid']);
        return $action_result;
    }
}
