<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/10/31
 * Time: 16:08
 */
class UserService extends Manager
{
    /**
     * 返回分销人员的账户
     *
     * @param $uid
     *
     * @return array
     */
    public function getSellerAccount($uid)
    {
        $sql = "SELECT
    SUM(if(play_distribution_detail.sell_type = 1 AND play_distribution_detail.sell_status = 2, 1, 0)) AS order_number,
    SUM(if(play_distribution_detail.sell_type = 1 AND play_distribution_detail.sell_status = 2, play_distribution_detail.price, 0)) AS total_sell,
    SUM(if(play_distribution_detail.sell_type = 2 AND play_distribution_detail.sell_status = 1, play_distribution_detail.rebate, 0)) AS not_arrived_income,
    SUM(if(play_distribution_detail.sell_type = 2 AND play_distribution_detail.sell_status = 2, play_distribution_detail.rebate, 0)) AS have_arrived_income,
    SUM(if(play_distribution_detail.sell_type = 2 AND play_distribution_detail.sell_status = 3, play_distribution_detail.price, 0)) AS total_sell_back,
    SUM(if(play_distribution_detail.sell_type = 3 AND play_distribution_detail.sell_status = 2, play_distribution_detail.price, 0)) AS withdraw_cash,
    SUM(if(play_distribution_detail.sell_type = 4 AND play_distribution_detail.sell_status = 2, play_distribution_detail.price, 0)) AS deduct_cash
FROM
	play_distribution_detail
WHERE
	sell_user_id = {$uid}";

        $account_data = Yii::app()->db->createCommand($sql)->queryRow();

        //说明 not_arrived_income 未到收益 have_arrived_income 已到收益  withdraw_cash 已提现金额 deduct_cash 管理员扣款

        $data = array(
            'account_money'       => bcsub(bcsub($account_data['have_arrived_income'], $account_data['withdraw_cash'], 2), $account_data['deduct_cash'], 2),
            'add_up_income'       => bcsub(bcadd($account_data['not_arrived_income'], $account_data['have_arrived_income'], 2), $account_data['deduct_cash'], 2),
            'not_arrived_income'  => bcadd($account_data['not_arrived_income'], 0, 2),
            'withdraw_cash'       => bcadd($account_data['withdraw_cash'], 0, 2),
            'total_sell'          => bcadd($account_data['total_sell'], 0, 2),
            'have_arrived_income' => bcadd($account_data['have_arrived_income'], 0, 2),
            'deduct_cash'         => bcadd($account_data['deduct_cash'], 0, 2),
            'order_number'        => $account_data['order_number'],
            'total_sell_back'     => bcadd($account_data['total_sell_back'], 0, 2),
        );

        return $data;

    }

    public function getGoodsListCount($city)
    {
        $data  = array(
            'count_goods'    => 0,
            'count_activity' => 0,
        );
        $timer = time();

        $id_goods_sql = "SELECT
	play_game_price.gid
FROM
	play_game_price
INNER JOIN play_game_info ON play_game_info.pid = play_game_price.id
INNER JOIN play_organizer_game ON play_organizer_game.id = play_game_price.gid
WHERE
	play_game_price.single_income > 0
AND play_game_info.`status` = 1
AND play_game_info.buy < play_game_info.total_num
AND play_organizer_game.`status` > 0
AND play_organizer_game.is_together = 1
AND play_organizer_game.city = '{$city}'
AND play_organizer_game.down_time >= {$timer}
AND play_organizer_game.up_time <= {$timer}
GROUP BY
	play_organizer_game.id";

        //todo 限制活动条件 及 场次条件 及城市
        $id_activity_sql = "SELECT
	play_excercise_base.id
FROM
	play_excercise_price
INNER JOIN play_excercise_event ON play_excercise_event.id = play_excercise_price.eid
INNER JOIN play_excercise_base ON play_excercise_base.id = play_excercise_event.bid
WHERE
	play_excercise_price.single_income > 0
AND play_excercise_base.city = '{$city}'
AND play_excercise_base.release_status= 1
AND play_excercise_event.sell_status>=1
AND play_excercise_event.sell_status!=3
AND play_excercise_event.start_time >(UNIX_TIMESTAMP()-1209600)
AND play_excercise_event.over_time>UNIX_TIMESTAMP()
AND play_excercise_event.customize = 0
AND play_excercise_event.join_number < play_excercise_event.perfect_number
GROUP BY
	play_excercise_base.id";

        $idGoodsData = Yii::app()->db->createCommand($id_goods_sql)->queryAll();;
        $idActivityData = Yii::app()->db->createCommand($id_activity_sql)->queryAll();;

        $data['count_goods']    = count($idGoodsData);
        $data['count_activity'] = count($idActivityData);

        return $data;
    }

    /**
     * 验证是否是分销员
     *
     * @param $uid
     *
     * @return bool
     */
    public function isRight($uid)
    {
        $res = UserVo::model()->find('uid = :uid', array(':uid' => $uid));
        if (!$res || !($res['is_seller'] == 1)) {
            return false;
        }
        return true;
    }

    public static function getVipChargeNotice($vip_session_arr, $total)
    {
        $param = '';
        for ($i = 0, $len = count($vip_session_arr); $i < $len; $i++) {
            if ($i == $len - 1) {
                $vo                    = $vip_session_arr[$i];
                $param['package_info'] = $vo['price'] . '元现金+' . $vo['free_number'] . '次免费亲子游';
            } else {
                $start_vo = $vip_session_arr[$i];
                $end_vo   = $vip_session_arr[$i + 1];

                $start = $start_vo['price'];
                $end   = $end_vo['price'];

                if ($total < $start) {
                    $param['package_info'] = '';
                    break;
                } else if ($total >= $start && $total < $end) {
                    $param['package_info'] = $start_vo['price'] . '元现金+' . $start_vo['free_number'] . '次免费亲子游';
                    break;
                }
            }
        }
        return $param['package_info'];
    }
}