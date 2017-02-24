<?php

/**
 * Created by IntelliJ IDEA.
 * User: deyi
 * Date: 2016/8/15
 * Time: 16:50
 */
class TicketController extends Controller{

    public function filters(){
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    /*买票首页*/
    public function actionBuyTicket()
    {
        $coupon_list_temp = TicketLib::tagList();
        $res_temp = TicketLib::areaList();

        $tag_list = array(
            '0' => array(
            'id' => 0,
            'name' => '全部分类'
            )
            );

            if($coupon_list_temp['data']['tag']){
                $tag_list = array_merge($tag_list, $coupon_list_temp['data']['tag']);
            }

            $result['tag_list'] = $tag_list;

            $area_list = array(
            '0' => array(
                'id' => 0,
                'rid' => 0,
                'name' => '全部区域',
                'child' => array(),
            ),
            '1' => array(
                'id' => 0,
                'rid' => 0,
                'name' => '附近',
                'child' => array(),
            ),
            );

            if($res_temp['data']['area']){
                $area_list = array_merge($area_list, $res_temp['data']['area']);
            }

            for ($i=0; $i<count($area_list); $i++){
                if (count($area_list[$i]['child'])==0){
                    $area_list[$i]['flag_child'] = 0;
                }else{
                    $area_list[$i]['flag_child'] = 1;
                }
            }
            $result['area_list'] = $area_list;
            $this->tpl->assign('res', $result);
            $this->tpl->display('ticket/m/buy_ticket.html');
    }

    /*商品详情*/
    public function actionCommodityDetail()
    {
        $lib  = new TicketLib();
        $data = new TicketData();

        $id  = intval($_GET['id']);
        $sid = intval($_GET['sid']);
        $user_vo = UserLib::getNowUser();
        $uid = $user_vo['uid'];
        $group_id = intval($_GET['group_id']);
        $verify_code = $_GET['verify_code'] ? $_GET['verify_code'] : 0;
        $seller_id = intval($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

        if($seller_id && $seller_id != 0){
            if($user_vo){
                $user_id = $user_vo['uid'];
                $sell_param = array(
                    'uid' => $user_id,
                    'seller_id' => $seller_id,
                    'id' => $id,
                    'type' => 'goods',
                );

                SellLib::shareClick($sell_param);
            }
        }

        // 获取商品数据
        $organizer_game_vo = OrganizerGameVo::model()->find('id = :id', array(':id' => $id));
        if (!$organizer_game_vo or !$organizer_game_vo->id) {
            exit('<h1>商品不存在</h1>');
        }

        $res_temp = $lib->commodityDetail(array('id' => $id));
        $res = $res_temp['data'];

        $custom_service_data = MemberLib::init();
        $res['custom_service'] = $custom_service_data['data'];
        //        DevUtil::e($res);
        if ($group_id) {
            $game_info_vo = GroupBuyVo::model()->find('id = :id', array(':id' => $group_id));
            $group_buy_vo = GroupBuyVo::model()->find('id = :id and game_id = :game_id', array(':id' => $group_id, ':game_id' => $id));
            $order_info_vo = OrderInfoVo::model()->find('user_id = :uid and group_buy_id = :gid', array(':uid' => $uid, ':gid' => $group_id));

            $game_info_id = $game_info_vo->game_info_id;

            $res['group_id'] = $group_id;

            if ($order_info_vo) {
                $info['user_group_buy_status'] = 1;
            }
            if (!$group_buy_vo) {
                exit('<h1>拼团信息不存在</h1>');
            }

            $res['group_info']          = array();
            $res['group_user']          = $group_buy_vo->uid;
            $res['group_price']         = $organizer_game_vo->g_price;
            $res['group_status']        = $group_buy_vo->status;
            $res['group_end_time']      = $group_buy_vo->end_time;
            $res['not_group_price']     = $data->getGameInfoVo($game_info_id)->price;
            $res['group_join_number']   = $group_buy_vo->join_number;
            $res['group_limit_number']  = $group_buy_vo->limit_number;
            $res['group_game_info_id']  = $group_buy_vo->game_info_id;
            $res['group_game_info_pid'] = $data->getGameInfoVo($group_buy_vo->game_info_id)->pid;

            $list = array();

            foreach ($data->getUserData($group_id) as $v) {
                $list[] = array('username' => $v['username'], 'userimg' => StringUtil::formatImageUrl($v['img']), 'uid' => $v['user_id']);
            }
            $res['group_info'] = $list;

        }

        $res['id'] = $id;
        $res['is_private'] = $organizer_game_vo->is_private_party;
        $res['information'] = htmlspecialchars_decode($organizer_game_vo->information);

        // 获取该商品可参团信息
        $group_buy_vo = null;
        $group_buy_vo = $data->getGroupBuyVo($id);
        $group_buy_num = $data->getGroupBuyVoNum($id);

        if ($group_buy_vo){
            foreach ($group_buy_vo as $v) {
                $v['img'] = StringUtil::formatImageUrl($v['img']);
            }
        }

        $res['group_num'] = $group_buy_num;
        $res['group_data'] = $group_buy_vo;

        //用户该商品是否参团和结团
        if ($organizer_game_vo->g_buy == 1) {
            $group_order_data = $data->getGroupBuyVoAndOrderInfoVo($uid, $id);
            if ($group_order_data) {
                $res['user_group_info'] = $group_order_data;
            }
        }

        // 判断最外层购买状态
        $low_price      = null;
        $new_status     = 0;
        $sell_status    = 0;
        $start_status   = 0;
        $qualify_status = 0;

        foreach ((array)$res['game_order'] as $v) {
            if ($v['buy_qualify'] == 1 && $v['new_user_buy'] < 2 && $v['up_time'] < time() && $v['down_time'] > time() && $v['surplus_num'] > 0) {
                $sell_status += 1;
            }
            if ($v['up_time'] > time()) {
                $start_status += 1;
            }
            if ($v['down_time'] > time() and $v['buy_qualify'] == 0 and $v['surplus_num'] > 0) {
                $qualify_status += 1;
            }
            if ($v['new_user_buy'] == 2) {
                $new_status += 1;
            }

            if ($low_price == null) {
                $low_price = $v['price'];
            } else {
                if ($v['price'] < $low_price) {
                    $low_price = $v['price'];
                }
            }
        }

        $res['low_price']      = $low_price;
        $res['new_status']     = $new_status;
        $res['sell_status']    = $sell_status;
        $res['start_status']   = $start_status;
        $res['qualify_status'] = $qualify_status;

        //客服数据
        $res['service_info'] = MemberService::postServiceInfo($res, 1, $res['id']);
        $res['verify_code'] = $verify_code;
        $res['seller_id'] = $seller_id;
        $this->tpl->assign('res', $res);
        $this->tpl->display('ticket/m/commodity_detail.html');
    }

    /*商品时间地点套系选择*/
    public function actionCommoditySelect()
    {
        $lib = new TicketLib();

        $tips         = intval($_GET["tips"]) ? intval($_GET["tips"]) : 0;
        $g_buy        = intval($_GET['g_buy']) ? intval($_GET["g_buy"]) : 0;//是否团购\
        $good_num     = intval($_GET["good_num"]) ? intval($_GET["good_num"]) : 1;
        $order_id     = intval($_GET['order_id']) ? intval($_GET["order_id"]) : 0;
        $coupon_id    = intval($_GET['coupon_id']) ? intval($_GET["coupon_id"]) : 0;
        $group_buy    = intval($_GET['group_buy']) ? intval($_GET["group_buy"]) : 0; //参团2
        $group_buy_id = intval($_GET['group_buy_id']) ? intval($_GET["group_buy_id"]) : 0;

        //        $param = $_POST['param'];
        $param = array(
            'coupon_id' => $coupon_id,
            'order_id' => $order_id,
            'g_buy' => $g_buy,
            'g_buy_id' => $group_buy_id
        );

        $res_temp = $lib->commoditySelect($param);
        $res = $res_temp['data'];

        if ($res['game_order']){
            $game_order_time  = array();
            $game_order_place = array();
            foreach ($res['game_order'] as $row){
                $game_order_time[$row['s_time'].'&'.$row['e_time']][] = $row;
                $game_order_place[$row['shop_name'].'&'.$row['address']][] = $row;
            }
            $res['game_order_time'] = $game_order_time;
            $res['game_order_place'] = $game_order_place;
        }

        $res['param'] = $param;

        $user_info = UserWapLib::getUserInfo();
        $my_free_coupon_number = $user_info['data']['free_number'];
        $res['is_vip_banner'] = $my_free_coupon_number>0 ? 0:1;
        $this->tpl->assign('res', $res);
        //        DevUtil::e($res);
        $this->tpl->display('ticket/m/commodity_select.html');
    }

    /*商品订单详情*/
    public function actionCommodityOrder()
    {
        $order_sn = $_GET['order_sn'];
        $order_info_vo = OrderInfoVo::model()->find("order_sn = :order_sn and order_status = 1", array(":order_sn"=>$order_sn));
        $in = array(
            'id' => $order_info_vo['coupon_id'],
            'rid' => $order_sn
        );

        $res_temp = OrderWapLib::nhave($in);
        $res = $res_temp['data'];

        for ($i=0; $i<count($res['order_list']); $i++){
            if ($res['order_list'][$i]['zyb_code']){
                $res['order_list'][$i]['code'] = $res['order_list'][$i]['zyb_code'];
            }
        }

        // 判断是否是待支付订单
        if($order_info_vo['pay_status'] == 0){
            $param       = array(
                'order_status' => $_GET['order_status']
            );
            $res_info = UserWapLib::getOrderList($param);
            $data = $res_info['data'];
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['data']         = json_encode($data[$i]);
                $data[$i]['order_status'] = $_GET['order_status'];
            }
            $res['info'] = $data;
        }

        //判断客服是否显示
        $custom_service_data = MemberLib::init();
        $res['custom_service'] = $custom_service_data['data'];

        $this->tpl->assign('res', $res);
        $res['param'] = $in;

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

        //客服数据
        $res['service_info'] = MemberService::postServiceInfo($res, 3, $_GET['order_sn']);

        $this->tpl->assign('res', $res);
        $this->tpl->display('ticket/m/commodity_order.html');
    }

    public function actionGetAllCoupons()
    {
        $lib = new TicketLib();
        HttpUtil::out($lib->couponList($_POST));
    }

    public function actionGetAllCommodityDel()
    {
        $lib = new TicketLib();
        HttpUtil::out($lib->delPost($_POST));
    }

    public function actionGetAllCommoditySend()
    {
        $lib = new TicketLib();
        HttpUtil::out($lib->sendPost($_POST));
    }

    public function actionGetAllCommodityConsult()
    {
        $lib = new TicketLib();
        HttpUtil::out($lib->consult($_POST));
    }

    public function actionGetAllRecommend(){
        $lib = new TicketLib();
        HttpUtil::out($lib->reply($_POST));
    }

    public function actionGetAllCollect(){
        $lib = new TicketLib();
        HttpUtil::out($lib->collect($_POST));
    }

    public function actionGetAllDefaultAddr(){
        $lib = new TicketLib();
        HttpUtil::out($lib->defaultAddr($_POST));
    }

    public function actionGetAllDelAddress(){
        $lib = new TicketLib();
        HttpUtil::out($lib->delAddress($_POST));
    }

    public function  actionGetAllCalendar(){
        $lib = new TicketLib();
        HttpUtil::out($lib->calendar($_POST));
    }

    public function actionGetAllEditAddress(){
        $lib = new TicketLib();
        HttpUtil::out($lib->editAddress($_POST));
    }
}