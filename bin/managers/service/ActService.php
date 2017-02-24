<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/13
 * Time: 16:54
 */
class ActService extends Manager
{

    public $act_id = null;

    public function __construct($act_id)
    {
        $this->act_id = intval($act_id);
    }

    public function postGiftUserRecord($uid, $token, $cash_id, $msg = '诗歌扫码领券活动')
    {
        //同步现金券到个人中心
        $cash_url = YII_DEBUG ? 'http://119.97.180.103/' : 'https://api.wanfantian.com/';
        $award_arr = array(
            'id'    => $cash_id,
            'uid'   => $uid,
            'msg'   => $msg,
            'token' => $token,
        );
        $award_str = json_encode($award_arr);
        $award_str = SecretUtil::encrypt($award_str);
        $data = array('p' => $award_str);
        $proxy = new ProxyUtil('post', $cash_url . 'cashcoupon/index/fetch', $data, array('ver' => 10, 'city' => '武汉',));
        $award_result = $proxy->run();
        $award_json = json_decode($award_result, true);

        $res = $award_json['response_params'];
        return $res['status'];
    }

    public function putUserPhoneAct($phone, $uid)
    {
        $sql = "update ps_act_user_record
                set bind_phone = '{$phone}'
                where user_id = '{$uid}'";
        return Yii::app()->db->createCommand($sql)->execute();
    }


    public function postActUserRecord($type = 0, $uid, $phone, $act_id = 2)
    {
        // 0 新用户 1 老用户
        $gift_user_record = new ActUserRecordVo();
        $gift_user_record->log_date = TimeUtil::getNowDate();
        $gift_user_record->act_id = $act_id;
        $gift_user_record->user_id = $uid;
        $gift_user_record->bind_phone = $phone;
        $gift_user_record->user_type = $type;
        if ($type == 0) {
            $record = array(
                0 => array(
                    'cash_id'         => 89,
                    'lottery_cash_id' => 3,
                    'status'          => 0
                ),
                1 => array(
                    'cash_id'         => 89,
                    'lottery_cash_id' => 3,
                    'status'          => 0
                ),
                2 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
                3 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
            );
            $gift_user_record->records = json_encode($record);
        } else if ($type == 1) {
            $record = array(
                0 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
                1 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
                2 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
                3 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
                4 => array(
                    'cash_id'         => 90,
                    'lottery_cash_id' => 8,
                    'status'          => 0
                ),
            );
            $gift_user_record->records = json_encode($record);
        }
        $gift_user_record->created = TimeUtil::getNowDate();
        return $gift_user_record->save();
    }

    /**
     * 领奖操作获取
     * 获取数据库中json格式数据并依次进行领取
     *
     * @param $uid
     * @param $phone
     * @param $token
     *
     * @return array
     * author: MEX | mixmore@yeah.net
     */
    public function getCash($uid, $phone, $token)
    {
        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );

        $act_user_record = ActUserRecordVo::model()->find('user_id = :user_id and bind_phone = :bind_phone and act_id = 2', array(':user_id' => $uid, ':bind_phone' => $phone));
        $act_user_record->status = 1;
        $records = json_decode($act_user_record->records, true);
        for ($i = 0; $i < count($records); $i++) {
            if ($records[$i]['status'] == 0) {
                $flag = $this->postGiftUserRecord($uid, $token, $records[$i]['cash_id']);
                if ($flag) {
                    $records[$i]['status'] = 1;
                    $result['status'] = 1;
                    $result['msg'] = "领取成功";
                } else {
                    $act_user_record->status = 0;
                }
            }
        }

        $act_user_record->records = json_encode($records);
        $act_user_record->save();

        return $result;
    }

    /**
     * 获取已有领奖人数的统计数据
     *
     * @param $act_id
     *
     * @return array
     * author: MEX | mixmore@yeah.net
     */
    public function getCashSum($act_id)
    {
        $sql = "select user_type, count(user_type) as sum from ps_act_user_record where act_id={$act_id} group by user_type";
        $query = Yii::app()->db->createCommand($sql)->queryAll();
        $res = array();
        foreach ($query as $val) {
            if ($val['user_type'] == 1) {
                $res['old_sum'] = $val['sum'];
            } else {
                $res['new_sum'] = $val['sum'];
            }
        }
        return $res;
    }
}