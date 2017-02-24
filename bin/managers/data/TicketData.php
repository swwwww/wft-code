<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/19
 * Time: 11:15
 */
class TicketData extends Manager{

    public function getUserData($group_id){
        $sql = "select play_user.username,play_user.img,play_order_info.user_id
                from play_order_info
                left join play_user
                on play_user.uid=play_order_info.user_id
                where play_order_info.group_buy_id={$group_id}
                and order_status=1";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public function getGameInfoVo($param){
        return GameInfoVo::model()->find('id = :id', array(':id'=>$param));
    }

    public function getGroupBuyVo($id){
        $time = time();
        $sql = "select play_group_buy.*,play_user.img
                from play_group_buy
                left join play_user
                on play_user.uid=play_group_buy.uid
                left join play_order_info
                on play_order_info.group_buy_id=play_group_buy.id
                where play_group_buy.status=1
                and play_group_buy.end_time>{$time}
                and play_group_buy.game_id={$id}
                and play_order_info.pay_status >=2
                and play_order_info.order_status=1 limit 4";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function getGroupBuyVoNum($id){
        $time = time();
        $sql = "select count(play_group_buy.id) as num
                from play_group_buy
                left join play_user
                on play_user.uid=play_group_buy.uid
                left join play_order_info
                on play_order_info.group_buy_id=play_group_buy.id
                where play_group_buy.status=1
                and play_group_buy.end_time>{$time}
                and play_group_buy.game_id={$id}
                and play_order_info.pay_status >=2
                and play_order_info.order_status=1";
        $res = Yii::app()->db->createCommand($sql)->queryRow();
        return $res->num;
    }

    public function getGroupBuyVoAndOrderInfoVo($uid, $id){
        $uid = intval($uid);
        $time = time();
        $sql = "select play_group_buy.*,play_order_info.group_buy_id
                from play_order_info
                left join play_group_buy
                on play_group_buy.id=play_order_info.group_buy_id
                where user_id={$uid}
                and coupon_id={$id}
                and play_group_buy.end_time>{$time}
                and pay_status>=2
                and order_status=1
                order by play_group_buy.end_time desc";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }
}