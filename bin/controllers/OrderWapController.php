<?php

/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 5:51
 */
class OrderWapController extends Controller
{
    //收银台
    public function actionOrderCheckout()
    {
        UserLib::checkUserPhone();

        $data    = $_GET;
        $service = new OrderWapService();
        $user_vo = UserLib::getNowUser();
        $uid     = $user_vo['uid'];

        $coupon_id = intval($data['coupon_id']) ? intval($data["coupon_id"]) : 0;  // 活动或者商品的id
        $info_id   = intval($data['info_id']) ? intval($data["info_id"]) : 0;   // 场次或者套系的id

        // 公共参数解析
        $name           = $data['name'] ? $data["name"] : 0;
        $phone          = intval($data["phone"]) ? intval($data["phone"]) : 0;
        $address        = trim($data['address']);
        $associates_ids = $data['associates_ids'];

        $order_type    = intval($data['order_type']); // 订单类型
        $cashcoupon_id = intval($data['cashcoupon_id']) ? intval($data["cashcoupon_id"]) : 0;  // 获取该订单可用的最大金额现金券

        $order_data = array();
        $pay_price  = 0;
        if ($order_type == 1) {
            // 解析必要参数
            $message      = $data['message']; // 备注
            $number       = intval($data['number']) ? intval($data["number"]) : 0;
            $group_buy    = intval($data['group_buy']) ? intval($data["group_buy"]) : 0;
            $group_buy_id = intval($data['group_buy_id']) ? intval($data["group_buy_id"]) : 0;

            // 商品
            $info_id         = intval($data['info_id']) ? intval($data["info_id"]) : 0;
            $coupon_data     = OrganizerGameVo::model()->find("id = :id", array(":id" => $coupon_id));
            $order_game_data = GameInfoVo::model()->find("id = :id", array(":id" => $info_id));

            //            $back_url = "order/checkout?name={$name}&phone={$phone}&message={$message}&city={$city}";
            //            $back_url .= "&coupon_id={$coupon_id}&number={$number}&order_id={$info_id}&group_buy={$group_buy}&group_buy_id={$group_buy_id}";

            $pay_price  = $number * $order_game_data['price'];
            $order_data = array(
                'cash_coupon_id' => $cashcoupon_id,
                'group_buy_id'   => $group_buy_id,
                'number'         => $number,
                'name'           => $name,
                'phone'          => $phone,
                'group_buy'      => $group_buy,
                'message'        => $message,
                'address'        => $address,
                'associates_ids' => $associates_ids,
                'type'           => $order_type - 1,
                'coupon_id'      => $coupon_id,
                'order_id'       => $info_id,
                'total'          => $pay_price,
                'use_score'      => $data['use_score']

                //            'back_url'       => $back_url,
            );
        } else if ($order_type == 2) {
            // 解析必要参数
            $charges   = json_decode($data['charges'], true);
            $pay_price = $data['total'];
            $meeting   = intval($data['meet_id']) ? intval($data["meet_id"]) : 0;

            // 活动
            $event_data = ExerciseEventVo::model()->find("id = :id", array(":id" => $info_id));
            $base_data  = ExerciseBaseVo::model()->find("id = :id", array(":id" => $event_data->bid));

            $coupon_data = array(
                'title'   => $base_data->name,
                'id'      => $event_data->id,
                'base_id' => $base_data->id
            );

            $order_game_data = array(
                'start_time' => $event_data->start_time,
                'end_time'   => $event_data->end_time
            );

            // $back_url .= "&session_id={$sid}&address={$address}&associates_ids={$associates_ids}&total={$total}";
            $order_data = array(
                'cash_coupon_id' => $cashcoupon_id,
                'name'           => $name,
                'phone'          => $phone,
                'address'        => $address,
                'associates_ids' => $associates_ids,
                'session_id'     => $info_id,
                'type'           => $order_type - 1,
                'meeting_id'     => $meeting,
                'charges'        => $charges,
                'coupon_id'      => $coupon_id,
                'order_id'       => $info_id,
                'total'          => $pay_price
                //            'back_url'       => $back_url,
            );
        }

        //        $author_url = "http://" . $_SERVER['HTTP_HOST'] . "/" . $back_url;

        if ($data['repay'] == 1) {
            $order_data['order_sn'] = $data['order_sn'];
        }


        $param = array(
            'coupon_id'            => $coupon_id,
            'info_id'              => $info_id,
            'type'                 => $order_type,
            'need_cashcoupon_list' => 1,
            'need_method'          => 1,
            'need_residual_amount' => 1,
            'pay_price'            => $pay_price,
        );

        if ($data['repay'] == 1) {
            $param['order_id'] = $data['order_sn'];
        }

        $res_temp    = OrderWapLib::checkStand($param);
        $check_stand = $res_temp['data'];

        if ($order_type == 2) {
            if ($data['repay'] == 1) {
                $vip_cost = MemberService::getVipParams($check_stand['members'], $check_stand['most_free_buy_same_day_number'], $check_stand['my_free_coupon_number']);
            }else{
                $vip_cost = MemberService::getVipParams($check_stand['members'], $check_stand['most_free_buy_same_day_number'], $check_stand['my_free_coupon_number'], $charges);
            }
        }
        $res = array(
            'coupon_data'     => $coupon_data,
            'order_game_data' => $order_game_data,
            'surplus'         => $service->getUserMoney($uid),
            'total'           => $pay_price,
            'data'            => json_encode($order_data),
            'pay_method'      => $check_stand['method'],
            'coupon_list'     => $check_stand['cashcoupon_list'],
            'vip_params'      => $vip_cost
            //            'share_order_sn' => $share_order_sn,
        );

        if ($data['repay'] == 1) {
            $res['total'] = $data['total'];
        }

        $res_temp = UserWapLib::getUserInfo();
        $res['info'] = $res_temp['data'];
        $res['param'] = $param;
        $this->tpl->assign('res', $res);
//        DevUtil::e($res);exit;
        $this->tpl->display('order/m/check_out.html');
    }

    // pay
    public function actionOrderPay()
    {
        // get order sn
        $data    = $_POST;
        $service = new OrderWapService();

        // set order
        $order_info = null;
        $order_sn   = "";
        if (isset($data['order_sn'])) {
            // 已有订单重新支付
            $order_sn = $data['order_sn'];
        } else {
            // 不存在订单
            $order_info_tmp = $service->getOrderSn($data);
            //$pay_status 0 密码错误 2 其他支付方式
            $pay_status = $order_info_tmp['data']['status'];
            if ($pay_status == -1 || $pay_status == 0 || $pay_status == 2) {
                HttpUtil::out($order_info_tmp);
            } else if ($pay_status == 1) {
                // 跳转其他支付方式 这里已经拿到了订单编号
                $order_sn = $order_info_tmp['data']['order_sn'];
            }
        }

        if ($data['use_account_money'] == 1) {
            // 使用余额重新支付
            $user_vo            = UserLib::getNowUser();
            $data['uid']        = $user_vo['uid'];
            $data['order_type'] = $data['type'];
            $params             = array(
                'uid'               => $user_vo['uid'],
                'order_sn'          => $data['order_sn'],
                'order_type'        => $data['type'] + 1,
                'use_account_money' => $data['use_account_money'],
                'pay_password'      => $data['pay_password']
            );
            $order_pay_res      = OrderWapLib::updateOrder($params);
        } else {
            // 使用微信或者支付宝支付
            $params        = array(
                'order_sn' => $order_sn,
                'pay_type' => intval($data['pay_type']) ? intval($data["pay_type"]) : 0,
                'type'     => $data['type'],
                'res'      => 'common_pay',
            );
            $order_pay_res = $service->orderPay($params);
        }
        HttpUtil::out($order_pay_res);
    }

    public function actionOrderClean()
    {
        $in  = array(
            'order_sn' => $_POST['order_sn']
        );
        $res = OrderWapLib::orderClean($in);
        HttpUtil::out($res);
    }

    public function actionNoPayInfo()
    {
        HttpUtil::out(OrderWapLib::noPayInfo($_POST));
    }

    //play detail order_detail
    public function actionOrderPlayDetail()
    {
        $lib = new OrderWapLib();
        $in  = array(
            'order_sn' => $_GET['order_sn']
        );

        $res_temp = $lib->orderPlayDetail($in);

        $res = $res_temp['data'];
        $res['order_sn'] = $_GET['order_sn'];
        $res['time'] = time();

        // 判断是否是待支付订单
        if($res['pay_status'] == 0){
            $param       = array(
                'order_status' => $_GET['order_status']
            );

            $res_info = UserWapLib::getOrderList($param);
            $data = $res_info['data'];
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['data']         = json_encode($data[$i]);
            }
            $res['info'] = $data;
        }

        //获取需要填写出行人的总数
        $join_len = 0;
        for($i = 0; $i < count($res['member_order_list']); $i++){
            if($res['member_order_list'][$i]['status'] == 0){
                ++$join_len;
            }
        }
        $res['join_len'] = $join_len;

        // 如果是支付成功回调回来的 这里需要判断
        if ($_GET['share'] == 1){
            $res_temp = ShareLib::payShare();
            $share_res = $res_temp['data'];
            $res['share'] = $share_res;

            $user_vo = UserLib::getNowUser();
            $user_id = intval($user_vo['uid']);
            $lottery_id = LotteryService::getVipLotteryId();//vip抽奖活动
            $service_share = new ShareConfigService($lottery_id);
            $wechat_share = $service_share->getShareConfigForVip($user_id, 1);
            $this->tpl->assign('wechat_share', json_encode($wechat_share));

            $this->tpl->assign('wechat_flag', $share_res['share']);
        }

        //判断客服是否显示
        $custom_service_data = MemberLib::init();
        $res['custom_service'] = $custom_service_data['data'];
        //客服数据
        $res['service_info'] = MemberService::postServiceInfo($res, 4, $_GET['order_sn']);
        $this->tpl->assign('res', $res);
        $this->tpl->display('order/m/order_detail.html');
    }

    //遛娃活动下单成功buy_completed
    public function actionOrderPlayCompleted()
    {
        $lib = new OrderWapLib();
        $in  = array(
            'order_sn' => $_GET['order_sn']
        );

        $res = $lib->orderPlayCompleted($in);

        $this->tpl->assign('res', $res['data']);
        $this->tpl->display('order/m/buy_completed.html');
    }


    public function actionGetAllOrderWap()
    {
        $lib = new OrderWapLib();
        // todo 优化接收参数的方式
        HttpUtil::out($lib->orderPlayDetail($_POST));
        HttpUtil::out($lib->orderPlayCompleted($_POST));

    }

    public function actionBackPay(){
        HttpUtil::out(OrderWapLib::backPay($_POST));
    }

    public function actionOrderSelectTraveller()
    {
        $res['associates_ids'] = '[,]';
        $res['data']           = json_encode($_GET);
        $travellers = PlayLib::playGetTravellers();
       
        $this->tpl->assign('res', $res); /*选择出行人*/
        $this->tpl->display('order/m/order_select_traveller.html');
    }

    // 补充出行人信息
    public function actionAddAssociates()
    {
        HttpUtil::out(OrderWapLib::addAssociates($_POST));
    }

    // 新版的充值页面 之后替换掉旧的页面
    public function actionChargeMoney()
    {
        UserLib::checkUserPhone();
        $data = $_POST;
        if (isset($data) && $data != NULL) {
            // 进入充值操作
            $user_vo = UserLib::getNowUser();
            $data['open_id'] = $user_vo['wechat_user']['open_id'];

            $res = UserWapLib::chargeMoney($data);

            $res_data = $res['data'];

            if ($res['status'] == 0) {
                $res['msg'] = '当前网络不稳定,无法完成充值';
                HttpUtil::out($res);
            }
            $params = array(
                'order_sn'     => $res_data['order_sn'],
                'pay_type'     => intval($data['paytype']),
                'type'         => 9,
                'res'          => $res_data,
                'package_info' => $data['package_info']
            );

            $service       = new OrderWapService();
            $order_pay_res = $service->orderPay($params, $data['money']);
            HttpUtil::out($order_pay_res);
        } else {
            $res_free_temp      = MemberLib::getFreeCoupon();
            $res_user_temp      = UserWapLib::getUserInfo();
            $res_member_temp    = MemberLib::init();
            $res['is_vip']      = $res_user_temp['data']['is_vip'];
            $res['vip_session'] = $res_free_temp['data'];
            $res['show_recharge_vip'] = $res_member_temp['data']['show_recharge_vip'];

            // 读取from_uid
            if ($_SESSION['vip_share_user_id']){
                $res['from_uid'] = $_SESSION['vip_share_user_id'];
                LogUtil::info("[OrderWapController][chargeMoney][from_uid]: {$res['from_uid']}");
            }else{
                $res['from_uid'] = 0;
            }

            $channel = $_GET['channel'];
            if($channel == 'wft_seller'){
                $res['show_recharge_vip'] = 1;
            }

            $this->tpl->assign('res', $res);
            // 直接跳转充值页面
            $this->tpl->display('user/m/charge_money_vip.html');
        }
    }

    //下单成功分享现金券
    public function actionCouponShare()
    {
        $this->tpl->display('order/m/coupon_share_register.html');
//        $this->tpl->display('order/m/coupon_share.html');
    }
}