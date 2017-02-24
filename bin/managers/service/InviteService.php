<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/5
 * Time: 09:17
 */
class InviteService extends Manager
{
    /**
     * 根据订单活动应有的返利
     *
     * @param $order 分享者的订单
     *
     * @return int
     */
    public function getRebate($order)
    {
        $type = 2;
        $city = $order->order_city;
        $item = ExerciseEventVo::model()->find('id = :id', array(':id'=>$order->coupon_id));

        if (!$item->share_reward) {
            return 0;
        }
        $options = (object)CacheUtil::fromCacheData('D:share_cash:' . $type . $city, function () use ($city, $type) {
            $data = $this->_getPlayCashShareTable()->get(array('city' => $city, 'type' => $type));
            return $data;
        }, 24 * 3600, true);

        if (!$options) {
            return 0;
        }

        if (!$item and !$options->isall) {
            return 0;
        }

        $opt = json_decode($options->options);

        $cv = 0;
        if ($opt and $order) {
            $money = bcadd($order->real_pay, $order->account_money, 2);
            foreach ($opt as $o) {
                $price = $o[0];
                $pay = explode('-', $price);
                if ($money >= $pay[0] and $money < $pay[1]) {
                    $cv = $o[3];
                    break;
                }
            }
        } else {
            $cv = 0;
        }
        return $cv;
    }

    public function WeiXinMsg($from_uid, $to_uid, $money, $type, $sn)
    {
        $users = $this->_getPlayUserWeiXinTable()->fetchAll(array('uid' => array($to_uid, $from_uid), 'appid' => 'wx8e4046c01bf8fff3'), array(), 2)->toArray();

        if (count($users) < 1) {
            return false;
        }
        $nickname = '';
        $open_id = 0;
        $users = (array)$users;

        foreach ($users as $u) {
            if ((int)$u['uid'] === $from_uid) {
                $nickname = $u['nickname'];
            }
            if ((int)$u['uid'] === $to_uid) {
                $open_id = $u['open_id'];
            }
        }

        $weixin = new WeiXinFun($this->getwxConfig());
        $msgstr = '';
        $data['touser'] = $open_id;
        if ($type === 'buy') {
            $msgstr = "【{$nickname}】已购买玩翻天活动，{$money}元现金返至您的玩翻天账户";
        } elseif ($type === 'use') {
            $msgstr = "【{$nickname}】已成功参加玩翻天活动，{$money}元现金已返至您的玩翻天账户";
        } elseif ($type === 'back') {
            $msgstr = "【{$nickname}】退订了玩翻天活动，您未能获得{$money}元分享奖励。再介绍多的朋友来玩翻天遛娃吧~";
        }
        $data['cash'] = $money;
        $data['order_sn'] = $sn;
        $data['open_id'] = $open_id;

        $tpm = $weixin->getTemplate('use', $data, $msgstr);

        $weixin->set_http_message($tpm);
    }

    public function GetuiMsg($from_uid,$to_uid,$money,$msg){
        $users = $this->_getPlayUserWeiXinTable()->get(array('uid'=>array($to_uid,$from_uid)));
        if(count($users) < 2){
            return false;
        }
        $nickname = '';$uid = 0;
        foreach($users as $u){
            if($u['uid'] === $from_uid){
                $nickname = $u['nickname'];
            }
            if($u['uid'] === $to_uid){
                $uid = $u['open_id'];
            }
        }
        $msgstr = '';
        if($msg === 'buy'){
            $msgstr = "【{$nickname}】已购买玩翻天活动，{$money}元现金将在Ta参加活动后返至您的玩翻天账户";
        }elseif($msg === 'use'){
            $msgstr = "【{$nickname}】已成功参加玩翻天活动，{$money}元现金已返至您的玩翻天账户";
        }elseif($msg === 'back'){
            $msgstr = "【{$nickname}】退订了玩翻天活动，您未能获得{$money}元分享奖励。再介绍多的朋友来玩翻天遛娃吧~";
        }
        $content = array(
            'title' => htmlspecialchars_decode('分享活动得返利', ENT_QUOTES),
            'info' => htmlspecialchars_decode($msgstr, ENT_QUOTES),
            'type' => 10,
            'id' => 0,
            'time' => time()+10,
        );


        if ($uid) {
            $geTui = new GeTui();
            $user_data = $this->_getPlayUserTable()->get(array('uid' => $uid));
            $token = $user_data->token;
            $str = substr($token, 0, 10);
            $mer = (array)$geTui->Push($uid . '__' . $str, htmlspecialchars_decode($msgstr, ENT_QUOTES), json_encode($content, JSON_UNESCAPED_UNICODE));
            if ($mer['result'] === 'ok') {
                return $this->_Goto('成功', '/wftadlogin/getui');
            }
            exit;
        }
    }

    public function middleware($order_sn,$flag = 0){
        if (empty($order_sn)) {
            return 0;
        }

        $data_order_info = OrderData::getOrderInfoByOrderSn($order_sn);

        if ($flag || ($data_order_info && ((int)$data_order_info['pay_status'] === 2 || (int)$data_order_info['pay_status'] === 5))) {
            $data_owner = $data_order_info;
        } else {
            return (int)false;
        }

        //是否属于分享
        $data_count = 0;
        $model_commodity_and_activity = new CommodityAndActivityData();
        if ($data_order_type == 1) {
            $data_result_commodity = $model_commodity_and_activity->getCommodityById($data_order_info['coupon_id']);
            $data_count            = $data_result_commodity['cash_share'];
        } elseif ($data_order_type == 2) {
            $data_result_activity  = $model_commodity_and_activity->getActivityById($data_order_info['coupon_id']);
            $data_count            = $data_result_activity['share_reward'];
        }

        // 获取该类型商品在订单所在城市的分享规则
        $data_order_city = $data_order_info['order_city'];

        if ((int)$data_order_info['order_type'] === 1) {
            $data_order_type = 1;
        } elseif ((int)$data_order_info['order_type'] === 3) {
            $data_order_type = 2;
        } else {
            $data_order_type = 1;
        }

        $data_vo_cash_share = CashShareVo::model()->find('city = :city and type = :type', array(':city' => $data_order_city, ':type' => $data_order_type));
        // 需要 redis 缓存
        //$options = (object)RedCache::fromCacheData('D:share_cash:' .$type. $city, function () use ($city,$type) {
        //    $data = $this->_getPlayCashShareTable()->get(['city' => $city,'type'=>$type]);
        //    return $data;
        //}, 24 * 3600 * 7, true);

        if(empty($data_vo_cash_share)){
            return 0;
        }

        if(empty($data_count) && empty($data_vo_cash_share->isall)){
            return 0;
        }

        $data_share_options = json_decode($data_vo_cash_share->options);

        $data_cv = 0;
        if ($data_share_options && $data_owner) {
            $data_money = bcadd($data_owner['real_pay'], $data_owner['account_money'], 2);
            foreach ($data_share_options as $o) {
                $data_price = $o[0];
                $data_pay   = explode('-', $data_price);
                if ($data_money >= $data_pay[0] && $data_money < $data_pay[1]) {
                    $data_cv = 1;
                    break;
                }
            }
        }else{
            $data_cv = 0;
        }

        return $data_cv;
    }
}
