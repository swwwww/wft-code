<?php

/**
 * Class StatsService
 * author: MEX | mixmore@yeah.net
 */
class StatsService extends Manager
{

    public $lottery_id = null;
    public $lottery_vo = null;

    public function __construct($lottery_id = 1)
    {
        $this->lottery_id = intval($lottery_id);
        $this->lottery_vo = LotteryVo::model()->find('id = :id', array(
            ':id' => $lottery_id,
        ));
    }

    /**
     * 有多少人抽奖、中奖人数、真实中奖人数、领取奖品人数
     * $tyoe
     * 0 返回总抽奖人数
     * 1 中奖人数
     * 2 真实中奖人数
     * 3 领取奖品人数
     *
     * @param     $lottery_id
     * @param int $type
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function getLotteryUserRecordStatistics($lottery_id, $type = 0)
    {
        $sql = "select count(user_id) as num
                from ps_lottery_user_record
                where lottery_id = {$lottery_id} ";
        if ($type == 0) {
        } elseif ($type == 1) {
            $sql .= "and (is_win=1 or is_win=2)";
        } elseif ($type == 2) {
            $sql .= "and is_win=2";
        } elseif ($type == 3) {
            $sql .= "and status=1";
        }
        $source = Yii::app()->db->createCommand($sql)->queryRow();
        return $source['num'];
    }

    /** 分享点击的次数，按天汇总
     * @param $lottery_id
     * author: MEX | mixmore@yeah.net
     */
    public function getLotteryShareClickRecordByLotteryId($lottery_id)
    {
        $lottery_id = intval($lottery_id);
        $sql = "select count(*) as num, log_date
                from ps_lottery_share_click_record
                where lottery_id = {$lottery_id}
                group by log_date
                order by log_date desc";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    //获取真是中奖用户
    public function getLotteryStatsUsers()
    {
        $sql = "select *, record.status as record_status
                from ps_lottery_user_record as record
                left join play_user as users
                on record.user_id = users.uid
                left join ps_lottery_cash as cash
                on record.lottery_cash_id = cash.id
                where record.is_win = 2
                limit 1000 ";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    //获取参与新用户，老用户中奖个数
    public function getLotteryStatsUsersCount($type = 1, $cut_time,$lottery_id,$sign)
    {
        $sql = "select count(distinct users.uid) as num, log_date
                from ps_lottery_user_record as record
                left join play_user as users
                on record.user_id = users.uid
                where record.lottry_id = {$lottery_id} ";
        if ($type == 1) {
            // 新用户
            $sql .= " and users.dateline > {$cut_time} ";
        } elseif ($type == 0) {
            // 老用户
            $sql .= " and users.dateline <= {$cut_time} ";
        }
        if($sign == 1){
            //指示为1则分组
            $sql .= "group by record.log_date";
        }

        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    //获取参与新用户，老用户  获取的奖品细则
    public function getLotteryStatsUsersCountDetail($type = 1, $cut_time)
    {
        $sql = "select uid, user_alias, phone, dateline, child_old, cash.cash_id as cid, cash.type as cash_type, cash_name
                from ps_lottery_user_record as record
                left join play_user as u
                on record.user_id = u.uid
                left join ps_lottery_cash as cash
                on record.lottery_cash_id = cash.id";
        if ($type == 1) {
            // 新用户
            $sql .= " where u.dateline > {$cut_time} ";
        } elseif ($type == 0) {
            // 老用户
            $sql .= " where u.dateline <= {$cut_time} ";
        }
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    //获取剩余奖品数量
    public static function getLeftGiftTotal($lottery_id){
        $sql = "select SUM(total) as total , SUM(send_total) as left_total
                from ps_lottery_cash 
                where lottery_id = {$lottery_id}
                ";
        $source = Yii::app()->db->createCommand($sql)->queryRow();
        return $source;
    }

    //获取开始时间 todo 寻找字符转时间戳方法
    public static function getLotteryStartTime($lottery_id){

    }

    public static function changeStatus($lottery_record_id){
        $sql = "update ps_lottery_user_record set status = 1 where lottery_record_id = {$lottery_record_id}";
        $update = Yii::app()->db->createCommand($sql)->execute();
    }
    
    /**
     * 获取其他类别 需要人工联系的用户列表
     *
     */
    public static function getOtherTypeList($lottery_id){
        $sql = "SELECT
                    plur.id,
                    plur.log_date,
                    plur.user_id,
                    plur.lottery_id,
                    plur.lottery_cash_id,
                    plur.status,
                    pu.username,
                    pu.phone
                FROM
                    ps_lottery_user_record as plur
                INNER JOIN play_user as pu
                ON plur.user_id = pu.uid
                WHERE
                    lottery_id = {$lottery_id} AND lottery_cash_id = 64
                ORDER BY plur.log_date DESC";

        $record_vo = Yii::app()->db->createCommand($sql)->queryAll();
        return $record_vo;
}


}