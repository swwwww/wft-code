<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/15
 * Time: 17:33
 */
class IntegralService extends Manager {
    private $limit_item = '2,3,1,15,17,18';
    private $delete_item = '100,103,104,105,106,107,108,109';

    static $static_limit_item = array(1, 2, 3, 15, 17, 18);

    static $static_delete_item = array(100, 103, 104, 105, 106, 107, 108, 109);

    static $static_day_task_type = array(1, 2, 3, 4, 5, 8, 15, 22);

    static $static_new_task_type = array(11, 12, 13, 14);

    // 任务类型及积分操作类型的id
    static $static_integral_option_type = array(
        'play_place_share'            => 1,   // 游玩地分享
        'play_place_comment'          => 2,   // 游玩地评论
        'goods_share'                 => 3,   // 商品分享
        'goods_comment'               => 4,   // 商品评论
        'goods_buy'                   => 5,   // 商品购买
        'invite_friend'               => 6,   // 邀请好友
        'info_complete'               => 7,   // 完善资料
        'day_sign'                    => 8,   // 每日签到
        'series_sign'                 => 9,   // 连续签到
        'day_task_complete'           => 10,  // 完成每日任务
        'icon_change'                 => 11,  // 更换头像积分
        'baby_info_complete'          => 12,  // 补充宝宝资料
        'baby_icon_upload'            => 13,  // 上传宝宝头像
        'wx_bind'                     => 14,  // 微信号绑定
        'circle_utterance'            => 15,  // 圈子发言
        'circle_utterance_good'       => 16,  // 圈子发言获赞
        'goods_comment_good'          => 17,  // 商品评论获赞
        'play_place_comment_good'     => 18,  // 游玩地评论获赞
        'share_option'                => 19,  // 点击分享获积分
        'new_task'                    => 21,  // 新手任务额外
        'invited_register'            => 22,  // 接受邀请注册
        'use_order'                   => 23,  // 使用订单
        'wx_activity'                 => 24,  // 微信活动
        'invited_accept'              => 25,  // 接受邀请
        'activity_comment'            => 26,  // 活动评论
        'refund_deduct'               => 100, // 退款扣除积分
        'qualify_convert'             => 101, // 积分兑换资格券
        'goods_qualify'               => 102, // 购买商品消耗积分
        'circle_untterance_del_user'  => 103, // 删除圈子发言扣除积分   用户
        'circle_untterance_del_admin' => 104, // 删除圈子发言扣除积分   小编
        'day_task_deduct'             => 105, // 扣除每日奖励积分
        'goods_comment_del_user'      => 106, // 删除商品评论扣除积分   用户
        'goods_comment_del_admin'     => 107, // 删除商品评论扣除积分   小编
        'play_place_comment_del_user' => 108, // 删除游玩地评论扣除积分 用户
        'play_place_comment_del_admin'=> 109, // 删除游玩地评论扣除积分 小编
    );

    /**
     * 获取设置
     * @return int
     */
    public function getSetting()
    {
        $setting = $this->getMarketSetting();

        return $setting;
    }


    /**
     * 退货扣积分
     *
     * @deprecate 购买不给积分了
     * @gid       订单id
     *
     * @param $uid
     * @param $price
     * @param $city
     *
     * @return bool|int
     */
    public function returnGood($uid, $gid, $city) {

        if (!$uid || !$gid) {
            return false;
        }

        $data_score = 0;

        $s1 = $this->subIntegral($uid, 0, 1, 100, $gid, $city);

        if (!$s1) {
            return false;
        } else {
            //扣除每日任务奖励积分
            $this->dayIntegralDelete($uid, $city);
            return (int)$data_score;
        }
    }

    /**
     * 扣除积分
     *
     * @param        $uid           用户uid
     * @param int    $base_score  　基础积分
     * @param int    $award_score 　奖励积分
     * @param int    $action_type_id
     * @param int    $object_id   　获得积分的对象
     * @param string $city        　城市
     * @param int    $only        　商品id
     *
     * @return bool
     */
    public function subIntegral($uid, $base_score = 0, $award_score = 1, $action_type_id = 1, $object_id = 0, $city = 'WH', $only = 0, $desc = '') {

        if (!$uid || !$action_type_id || $base_score < 0 || $award_score < 0) {
            return false;
        }

        $transaction = Yii::app()->db->beginTransaction();

        //积分记录表添加记录
        $total_score = (int)$base_score * (int)$award_score;

        $desc = $desc ?: self::$tparr[$action_type_id];

        $u = IntegralUserVo::model()->find('uid = :uid', array(':uid'=>$uid));

        if (!$u) {
            // 初始化用户数据
            $integral_user_vo = new IntegralUserVo();
            $integral_user_vo->uid = $uid;
            $integral_user_vo->total = 0;
            $s1 = $integral_user_vo->save();
            if (!$s1) {
                return false;
            }
        } else {
            //应该扣除的积分
            if ((int)$u->total < (int)$total_score) {
                $total_score = (int)$u->total;
            }

            //积分明细
            $integral_vo = new IntegralVo();
            $integral_vo->uid = $uid;
            $integral_vo->type = $action_type_id;
            $integral_vo->total_score = $total_score;
            $integral_vo->base_score = (int)$base_score;
            $integral_vo->award_score = (int)$award_score;
            $integral_vo->object_id = $object_id;
            $integral_vo->create_time = time();
            $integral_vo->city = $city;
            $integral_vo->desc = $desc;
            $integral_vo->onlyid = $only;
            $s1 = $integral_vo->save();

            if (!$s1) {
                $transaction->rollback();
                return false;
            }

            if ($total_score) {
                $integral_user_vo = null;
                $integral_user_vo = IntegralUserVo::model()->find('uid = :uid', array(':uid' => $uid));
                $integral_user_vo->total -= $total_score;
                $s2 = $integral_user_vo->save();
                if (!$s2) {
                    $transaction->rollback();
                    return false;
                }
            }

        }

        $transaction->commit();

        return true;
    }

    /**
     * 扣除每日积分任务
     *
     * @param $uid
     * @param $city
     *
     * @return bool
     */
    public function dayIntegralDelete($uid, $city)
    {
        $start = strtotime(date('Y-m-d'));

        $all = IntegralVo::model()->find('uid = :uid and create_time >= :create_time', array(':uid'=>$uid, ':create_time'=>$start));
        $give = $minus = 0;
        if ($all) {
            foreach ($all as $d) {
                if ($d['type'] == 10) {//给过任务额外
                    $give++;
                }
                if ($d['type'] == 105) {//扣除任务额外
                    $minus++;
                }
            }
        }

        if ($give == 0 || $give <= $minus) {
            return false;
        }

        $task = TaskIntegralVo::model()->find('city = :city', array(':city'=>$city));
        if (!$task) {
            return false;
        }
        $day = unserialize($task->day_task);
        $arr = array();
        if ($day) {
            foreach ($day as $n) {
                if ((int)$n !== 22) {
                    $arr[] = $n;
                }
            }
        }

        //添加积分被扣除的情况 //4,2,5,15,100,103,104,105
        $give_goods = $minus_goods = $give_places = $minus_places = $give_buy = $minus_return = $give_circle = $minus_circle = 0;
        foreach ($all as $d) {
            if ($d['type'] == 4) {//商品评论
                $give_goods++;
            }
            if ($d['type'] == 106 || $d['type'] == 107) {//删去商品评论
                $minus_goods++;
            }
            if ($d['type'] == 2) {//游玩地评论 8 9
                $give_places++;
            }
            if ($d['type'] == 108 || $d['type'] == 109) {//游玩地评论 8 9
                $minus_places++;
            }
            if ($d['type'] == 5) {//购买商品积分
                $give_buy++;
            }
            if ($d['type'] == 100) {//退款获得积分
                $minus_return++;
            }
            if ($d['type'] == 15) {//购买商品积分
                $give_circle++;
            }
            if ($d['type'] == 103 || $d['type'] == 104) {//游玩地评论 8 9
                $minus_circle++;
            }
        }
        $delete = 0;
        if ($give_goods <= $minus_goods && in_array(4, $arr)) {
            $delete = 1;
        }

        if ($give_places <= $minus_places && in_array(2, $arr)) {
            $delete = 1;
        }

        if ($give_buy <= $minus_return && in_array(5, $arr)) {
            $delete = 1;
        }

        if ($give_circle <= $minus_circle && in_array(15, $arr)) {
            $delete = 1;
        }


        if ($delete) {
            $s1 = $this->subIntegral($uid, $task->day_plus, 1, 105, 0, $city);

            if (!$s1) {
                return false;
            } else {
                return $task->day_plus;
            }
        }
        return false;
    }

    /**
     * 购买商品获得积分
     * @param $uid       用户uid
     * @param $price 　  商品价格 ?小数如何处理
     * @param $order_sn  订单id
     * @param $gid       套系id
     * @param $city
     * @return bool|int
     */
    public function getIntegralForBuyGood($uid, $gid, $price, $city, $coupon_name='') {
        if (empty($price) || empty($uid) || empty($gid)) {
            return false;
        }

        $pdo         = Yii::app()->db;
        $transaction = $pdo->beginTransaction();

        $data_desc   = '购买' . ($coupon_name ? '' : '商品') . '获得积分';
        $data_score  = 0; // 目前购买商品不获得积分，所以获得的积分为0

        $vo_integral              = new IntegralVo();

        // 存储积分获取记录
        $vo_integral->uid         = $uid;
        $vo_integral->type        = 5;
        $vo_integral->total_score = $data_score;
        $vo_integral->base_score  = $data_score;
        $vo_integral->award_score = 1;
        $vo_integral->object_id   = $gid;
        $vo_integral->create_time = time();
        $vo_integral->city        = $city;
        $vo_integral->desc        = $data_desc;

        $data_result_insert_integral = $vo_integral->save();

        if (empty($data_result_insert_integral)) {
            $transaction->rollback();
            return false;
        } else {
            $transaction->commit();
        }

        //每日任务奖励积分
        $this->getIntegralForDaysTask($uid, $city);

        return $data_score;
    }

//    /**
//     * 每日积分任务
//     * @param $uid
//     * @param $city
//     * @return bool
//     */
//    public function getIntegralForDaysTask($uid, $city = 'WH') {
//        if (empty($uid)) {
//            return false;
//        }
//
//        $model_integralData = new IntegralData();
//        $model_inviteData   = new InviteData();
//
//        $select_param = array(
//            'uid' => $uid,
//            'start_time' => strtotime(date('Y-m-d')),
//        );
//
//        $data_return_integral_user = $model_integralData->getIntegralRecordByUserIdAndTime($select_param);
//
//        if ($data_return_integral_user['status']) {
//            $data_dotask = $data_return_integral_user['data'];
//            if ($data_return_integral_user['data']) {
//                //判断给过今日任务，或者扣除每日任务没有给的多
//                $give = 0; // 用户获得的积分
//                $get  = 0; // 用户被扣除的积分
//                foreach ($data_return_integral_user['data'] as $key => $val) {
//                    if ($val['type']==10) {
//                        $give++;
//                    } elseif ($val['type']==105) {
//                        $get++;
//                    }
//                }
//
//                if ($give > $get) {
//                    return false;
//                }
//            } else {
//                return false;
//            }
//        } else {
//            return false;
//        }
//
//        $select_param = array(
//            'city' => $city
//        );
//
//        $data_return_task_integral = $model_integralData->getTaskIntegralByCity($select_param);
//
//        if ($data_return_task_integral['status']) {
//            if (empty($data_return_task_integral['data'])) {
//                return false;
//            }
//        } else {
//            return false;
//        }
//
//        $data_day_task   = unserialize($data_return_task_integral['data']['day_task']); // 获得每日任务内容
//        $data_task_count = count($data_day_task);                                       // 任务总数
//
//        if (0 === $data_task_count || false === $data_day_task) {
//            return false;
//        }
//
//        if (in_array(self::STATIC_DAY_TASK_TYPE, $data_day_task)) {
//            //如果邀请注册是任务，则需要判断今天用户是否进行了邀约注册
//            $select_param = array(
//                'uid' => $uid,
//                'start_time' => strtotime(date('Y-m-d')),
//            );
//
//            $data_invite_record = $model_inviteData->getInviteRegisterRecordsByUserId($select_param);
//
//            if ($data_invite_record['status']) {
//                if (empty($data_invite_record['data'])) {
//                    return false;
//                }
//            } else {
//                return false;
//            }
//            $data_task_count--;
//        }
//
//        $days = '';
//        $arr[] = $uid;
//        $arr[] = $start;
//
//        if ($day) {
//            foreach ($day as $n) {
//                if ((int)$n !== 22) {
//                    $days .= '`type` = ? or ';
//                    $arr[] = $n;
//                }
//            }
//        }
//        if ($days === '') {
//            $days = "`type`=0";
//        } else {
//            $days = rtrim($days, " or ");
//        }
//        $today_task = $adapter->query("SELECT count(distinct(`type`)) as ct FROM play_integral
//      WHERE `uid`= ? and create_time >= ? and (" . $days . ")", $arr)->current();
//
//        if (0 === $count && !in_array(22, $day)) {
//            return false;
//        }
//
//        if (!$today_task || (int)$today_task->ct !== $count) {
//            return false;
//        }
//
//        //添加积分被扣除的情况 //4,2,5,15,100,103,104,105
//        $give_goods = $minus_goods = $give_places = $minus_places = $give_buy = $minus_return = $give_circle = $minus_circle =0;
//        foreach($dotask as $d){
//            if($d['type']==4){//商品评论
//                $give_goods++;
//            }
//            if($d['type']==106 || $d['type']==107){//删去商品评论
//                $minus_goods++;
//            }
//            if($d['type']==2){//游玩地评论 8 9
//                $give_places++;
//            }
//            if($d['type']==108 || $d['type']==109){//游玩地评论 8 9
//                $minus_places++;
//            }
//            if($d['type']==5){//购买商品积分
//                $give_buy++;
//            }
//            if($d['type']==100){//退款获得积分
//                $minus_return++;
//            }
//            if ($d['type'] == 15 ) {//圈子发言
//                $give_circle++;
//            }
//            if($d['type']==103 || $d['type']==104){//游玩地评论 8 9
//                $minus_circle++;
//            }
//        }
//
//        if($give_goods <= $minus_goods && in_array(4,$arr)){
//            return false;
//        }
//
//        if($give_places <= $minus_places && in_array(2,$arr)){
//            return false;
//        }
//
//        if($give_buy <= $minus_return && in_array(5,$arr)){
//            return false;
//        }
//
//        if($give_circle <= $minus_circle && in_array(15,$arr)){
//            return false;
//        }
//
//        //今天
//        $s1 = $this->addIntegral($uid, $task->day_plus, 1, 10, 0, $city);
//
//        if (!$s1) {
//            return false;
//        } else {
//            return $task->day_plus;
//        }
//    }
}
