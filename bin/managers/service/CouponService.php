<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/19
 * Time: 14:40
 */
class CouponService extends Manager
{
    /**
     * 购买获得票券
     *
     * @param $uid
     * @param $gid
     * @param $sid
     * @param $city
     */
    public function getCashCouponByBuy($uid, $gid, $sid, $order_sn, $city, $getinfo = '')
    {
        $adapter = $this->_getAdapter();
        $sql = "SELECT * FROM play_welfare left join play_welfare_cash ON play_welfare.welfare_link_id
= play_welfare_cash.id where gid = ? and good_info_id = ? and give_time = 1 and welfare_type = 3 and play_welfare_cash.status = 2;";
        $pw = $adapter->query($sql, array($gid, $sid))->toArray();
        if (false === $pw || count($pw) === 0) {
            return false;
        }

        foreach ($pw as $p) {
            if ($p['total_num'] > $p['give_num']) {
                $this->addCashcoupon($uid, $p['cash_coupon_id'], $order_sn, 5, 0, $getinfo, $city);
            }
        }
    }

    /**
     * 给团购的团长发放代金券奖励
     * @param $uid 用户id
     * @param $coupon_id （票券ｉｄ）
     * @param $get_object_id （来源id，如评论id，商品id）
     * @param $get_type （获取票券方式 领券原因类型 1兑换 2点评商品，３点评游玩地，４活动发放　５商品购买　６采纳攻略　７好评app 8圈子发言奖励 9后台评论奖励,10后台邀约有礼奖励,11使用验证）
     * @param $adminid （如果是管理员奖励，管理员id, 如果系统自动奖励则为0）
     * @param string $get_info (获得票券的描述)
     * @param string $city
     * @param string $get_order_id 订单
     * @return bool
     */
    public function addCashcoupon($uid, $coupon_id, $get_object_id, $get_type, $adminid, $get_info = '', $city = 'WH', $get_order_id = 0) {
        // 进行参数校验，缺乏必要的参数则不进行任何操作
        if (empty($uid) || empty($coupon_id) || empty($get_type)) {
            return false;
        }

        $get_type_arr = array(
            1  => '兑换码兑换',
            2  => '点评商品',
            3  => '点评游玩地',
            4  => '参加活动',
            5  => '商品购买',
            6  => '采纳攻略',
            7  => '好评app',
            8  => '圈子发言奖励',
            9  => '后台评论奖励',
            10 => '接受邀约有礼奖励',
            11 => '使用验证',
            12 => '地推活动',
            13 => '邀请朋友奖励',
            14 => '购买商品分享红包奖励',
            15 => '接受商品红包奖励',
            16 => '购买活动分享红包奖励',
            17 => '好友通过分享参加活动奖励',
            18 => '延期补偿',
            19 => '资深玩家奖励',
            20 => '好想你券'
        );

        $data_coupon_id = (int)$coupon_id;
        $data_get_type  = (int)$get_type;
        $data_adminid   = (int)$adminid;
        $data_get_info  = $get_info ? $get_info : ($get_type_arr[$get_type] ? $get_type_arr[$get_type] : ''); // 初始化获取代金券途径的描述

        $pdo            = Yii::app()->db;
        $sql            = "SELECT * FROM play_cash_coupon WHERE id = :id AND residue > 0 AND end_time > :time AND status = 1 AND is_close = 0 LIMIT 1";
        $sql_param      = array(
            'id'   => $data_coupon_id,
            'time' => time()
        );

        $data_return_cash_coupon = $pdo->createCommand($sql)->query($sql_param);

        if (empty($data_return_cash_coupon)) {
            return false;
        } elseif ($data_return_cash_coupon['new']) {
            // 如果是新用户专享则不向老用户发放该奖励代金券
            $sql = "select * from play_order_info where user_id = ? limit 1";
            $order = $adapter->query($sql, array($uid))->current();
            if($order){
                return false;
            }

            $sql       = "SELECT COUNT(*) AS count FROM play_order_info WHERE user_id = :uid";
            $sql_param = array(
                'uid'  => $uid
            );

            $data_return_count_order_user = $pdo->createCommand($sql)->query($sql_param);

            if (empty($data_return_count_order_user['count'])) {
                return false;
            }
        }

        $transaction = $pdo->beginTransaction();                      // 数据处理的事务开始

        // 更新代金券表中该项代金券的剩余数量
        $sql     = "UPDATE play_cash_coupon SET residue = residue - 1 WHERE `id` = :id AND residue > 0 ";
        $command = $pdo->createCommand($sql);
        // 绑定参数
        $command->bindParam(":id", $data_return_cash_coupon['id']);

        $data_return_update_cash_coupon = $command->execute();

        if (empty($data_return_update_cash_coupon)) {
            $transaction->rollback();
            return false;
        }

        //判断票券使用时间类型 使用时间的类别  0 固定周期 ，1 领券后到期（统一到小时为单位）
        if ($data_return_cash_coupon['time_type']) {
            $data_use_stime = time();
            $data_use_etime = time() + (int)$data_return_cash_coupon['after_hour'] * 3600;
        } else {
            $data_use_stime = (int)$data_return_cash_coupon['use_stime'];
            $data_use_etime = (int)$data_return_cash_coupon['use_etime'];
        }

        //票券记录表 管理员操作记录
        $sql  = 'INSERT INTO';
        $sql .= ' play_cashcoupon_user_link ';
        $sql .= '(cid, uid, create_time, use_stime, use_etime, get_info, get_object_id, get_type, adminid, city, title, price, get_order_id)';
        $sql .= ' VALUES ';
        $sql .= '(:cid, :uid, :time, :use_stime, :use_etime, :get_info, :object_id, :get_type, :adminid, :city, :title, :price, :order_id)';

        $command = $pdo->createCommand($sql);

        // 绑定参数
        $command->bindParam(":cid",       $data_coupon_id);
        $command->bindParam(":uid",       $uid);
        $command->bindParam(":time",      time());
        $command->bindParam(":use_stime", $data_use_stime);
        $command->bindParam(":use_etime", $data_use_etime);
        $command->bindParam(":get_info",  $data_get_info);
        $command->bindParam(":object_id", $get_object_id);
        $command->bindParam(":get_type",  $data_get_type);
        $command->bindParam(":adminid",   $data_adminid);
        $command->bindParam(":city",      $city);
        $command->bindParam(":title",     $data_return_cash_coupon['title']);
        $command->bindParam(":price",     $data_return_cash_coupon['price']);
        $command->bindParam(":order_id",  $get_order_id);

        //票券表插入一条记录
        $data_return_insert_cashcoupon_user_link = $command->execute();

        if (empty($data_return_insert_cashcoupon_user_link)) {
            $transaction->rollback();
            return false;
        } else {
            $transaction->commit();
            return $data_return_cash_coupon;
        }
    }
}
