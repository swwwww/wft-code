<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/5
 * Time: 09:20
 */
class AccountService extends Manager
{
    /**
     * 充值
     * @param $uid
     * @param float $money |充值金额
     * @param string $desc |充值描述
     * @param int $withdrawal |是否可提现 0不可 1可以提现
     * @param int $action_type_id |充值类型 1普通退款 2支付宝充值 3银联充值  4圈子发言奖励 5购买商品奖励　6点评商品奖励　7点评游玩地奖励　8　app好评奖励 9采纳攻略 10 使用验证返利, 11 后台评论管理奖励　12微信充值,　14地推首单,　15邀约首单, 16好友参与分享活动，分享者或奖励 17自然童趣充值 18 活动退还押金
     * @param int $object_id |关联对象id
     * @param bool $confirm | 是否需要确认充值,只有使用第三方充值时才使用.如果是确认充值需要再次调用successful函数后才会进入用户可提现账户
     * @param int $editor_id | 编辑id,操作对象id
     * @param string $city | 城市
     * @return bool
     */
    public function recharge($uid, $money = 0.00, $withdrawal = 0, $desc = '', $action_type_id = 1, $object_id = 0, $confirm = false, $editor_id = 0, $city = 'WH')
    {
        $money = (float)$money;
        if (!$uid || !$action_type_id || !$money || !$desc) {
            return false;
        }
        if ($money <= 0) {
            return false;
        }

        $u = AccountVo::model()->find('uid = :uid', array(':uid' => $uid));

        if ($u) {
            if ($u->status == 0) {
                return false;
            }
        }
        if (!$u) {
            $now_money = $money;
        } else {
            $now_money = bcadd($u->now_money, $money, 2);
        }
        $transaction = Yii::app()->db->beginTransaction();

        if (!$confirm) {
            if (!$u) {
                $account_vo = new AccountVo();
                $account_arr = array(
                    'uid'              => $uid,
                    'now_money'        => $money,
                    'can_back_money'   => $money,
                    'total_money_flow' => $money,
                    'last_time'        => time(),
                    'status'           => 1
                );
                //初始化用户数据
                if ($withdrawal == 1) {
                    $res = $account_vo->commonSave($account_arr);

                } else {
                    unset($account_arr['can_back_money']);
                    $res = $account_vo->commonSave($account_arr);
                }
                if (!$res) {
                    $transaction->rollback();
                    return false;
                }

            } else {
                $account_vo = AccountVo::model()->find('uid = :uid', array(':uid' => $uid));
                $account_vo->now_money += $money;
                $account_vo->last_time = time();
                $account_vo->total_money_flow += $money;
                if ($withdrawal == 1) {
                    $account_vo->can_back_money += $money;
                }
                if (!$account_vo->save()) {
                    $transaction->rollback();
                    return false;
                }
            }
        }

        if ($confirm) {
            $status = 0;
        } else {
            $status = 1;
        }
        $account_log_arr = array(
            'uid'                 => $uid,
            'action_type'         => 1,
            'action_type_id'      => $action_type_id,
            'object_id'           => $object_id,
            'flow_money'          => $money,
            'surplus_money'       => $now_money,
            'dateline'            => time(),
            'description'         => $desc,
            'status'              => $status,
            'editor_id'           => $editor_id,
            'city'                => $city,
            'withdraw'            => $withdrawal,
            'user_account'        => $uid,
            'check_status'        => 0,
            'can_back_money_flow' => $withdrawal ? $money : null
        );
        $account_log_vo = new AccountLogVo();
        if (!$account_log_vo->commonSave($account_log_arr)) {
            $transaction->rollback();
            return false;
        }

        $log_id = Yii::app()->db->lastInsertID;;
        $transaction->commit();
        CacheUtil::del('D:UserMoney:' . $uid);
        return $log_id;
    }

    /**
     * 购买获取返利
     * @param $uid
     * @param $gid
     * @param $sid
     * @param $order_sn
     * @param $city
     * @return bool
     */
    public function getCashByBuy($uid, $gid, $sid,$order_sn, $city,$coupon_name=''){
        $adapter = $this->_getAdapter();

        $sql = "SELECT * FROM play_welfare left join play_welfare_rebate ON play_welfare.welfare_link_id
= play_welfare_rebate.id where gid = ? and good_info_id = ? and give_time = 1 and welfare_type = 2 and play_welfare_rebate.status = 2;";

        $pw = $adapter->query($sql,array($gid,$sid))->toArray();

        if(false === $pw || count($pw) === 0){
            return false;
        }

        $coupon_name = $coupon_name?:'商品';

        foreach($pw as $p){
            if($p['total_num'] > $p['give_num']){
                $this->recharge($uid,($p['single_rebate']),((int)$p['rebate_type']-1),'购买'.$coupon_name.'获得返利',5,$order_sn,false,0,$city);
            }
        }
    }
}