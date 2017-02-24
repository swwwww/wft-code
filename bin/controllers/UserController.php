<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/17
 * Time: 16:11
 */
class UserController extends Controller
{

    public function filters()
    {
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller'   => $this,
        ));

    }

    //需要获取订单信息 code_sn (数据无法获取)
    public function actionCode()
    {
        $order_sn                  = $_GET['order_sn'];
        $order_info_vo             = OrderInfoVo::model()->find("order_sn = :order_sn and order_status = 1", array(":order_sn" => $order_sn));
        $in                        = array(
            'id'  => $order_info_vo['coupon_id'],
            'rid' => $order_sn
        );
        $res                       = OrderWapLib::nhave($in); /*活动订单*/
        $res['data']['order_type'] = $_GET['order_type'];
        $this->tpl->assign('res', $res['data']);/*活动订单*/
        $this->tpl->display('user/m/user_code_sn.html');
    }

    //宝宝列表详情 my_baby(ok)
    public function actionBabyList()
    {
        $res  = UserWapLib::getUserInfo();

        $this->tpl->assign('res', $res['data']['baby']);
        $this->tpl->display('user/m/user_baby.html');
    }

    //宝宝信息详情页面
    public function actionEditBaby()
    {
        $id           = intval($_GET['id']);
        $act          = $_GET['act'];
        $user_baby_vo = UserBabyVo::model()->find('id = :id', array(':id' => $id));
        //        DevUtil::e($user_baby_vo); exit();
        $res['baby_name']  = $user_baby_vo->baby_name;
        $res['baby_sex']   = $user_baby_vo->baby_sex;
        $res['baby_birth'] = $user_baby_vo->baby_birth;
        $res['img']        = $user_baby_vo->img;
        $res['id']         = $user_baby_vo->id;

        $this->tpl->assign('res', $res);
        //        $this->tpl->assign('link_id', $id);
        //        $this->tpl->assign('act', $act);
        $this->tpl->display('user/m/user_edit_baby.html');
    }

    //编辑我信息
    public function actionEditMyInfo()
    {
        if (isset($_POST) && $_POST != null){
            // 更新
            HttpUtil::out(UserWapLib::userInfoReset($_POST));
        }else{
            // 获取个人信息
            $res_temp = UserWapLib::getUserInfo();
            $res = $res_temp['data'];
            $this->tpl->assign('res', $res);
            $this->tpl->display('user/m/edit_my_info.html');
        }
    }

    //添加baby信息
    public function actionAddBaby()
    {
        $lib = new UserWapLib();
        $res = $lib->AddMyBaby($_POST);
        HttpUtil::out($res);
    }

    //上传用户图片
    public function actionUploadImg()
    {
        $res = UserWapLib::uploadImg($_POST);
        HttpUtil::out($res);
    }

    //个人中心my_center
    public function actionCenter()
    {
        $res_temp = UserWapLib::getUserInfo();
        $res      = $res_temp['data'];

        if ($res['sex'] == 1) {
            $res['sex_name'] = '宝爸';
            $res['sex_logo'] = '♂';
        } else if ($res['sex'] == 2) {
            $res['sex_name'] = '宝妈';
            $res['sex_logo'] = '♀';
        }
        $res_member_temp    = MemberLib::init();
        $res['show_my_center_vip'] = $res_member_temp['data']['show_my_center_vip'];
        $lottery_id = LotteryService::getVipLotteryId();//vip抽奖活动
        $service = new WinService($lottery_id);
        $res['left_total'] = $service->getWinUserLeftTotal($res['uid']);

//        DevUtil::e($res); exit();
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/user_center_vip.html');
    }

    //个人订单中心 my_order
    public function actionOrder()
    {
        $in       = array(
            'order_status' => $_GET['order_status']
        );
        $res_temp = UserWapLib::getOrderList($in);
        $res      = $res_temp['data'];
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]['data']         = json_encode($res[$i]);
            $res[$i]['order_status'] = $_GET['order_status'];
        }
        $res['order_status'] = $_GET['order_status'];
        $this->tpl->assign('res', $res);
//        DevUtil::e($res);exit;
        $this->tpl->display('user/m/user_order.html');
    }

    // 个人订单中心 my_order dao.js 调用这个获取数据
    public function actionGetAllOrders()
    {
        $res = UserWapLib::getOrderList($_POST);
        HttpUtil::out($res);
    }

    //删除订单
    public function actionDelOrder()
    {
        $res = UserWapLib::delOderItem($_POST);
        HttpUtil::out($res);
    }

    //我的积分 ok
    public function actionScores()
    {
        $user = new UserWapLib();

        $res_temp = $user->getUserPoints(null);

        $sid        = $_GET['id'];
        $res        = $res_temp['data'];
        $res['sid'] = $sid;
        //        DevUtil::e($res); exit();
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/user_score.html');
    }

    //签到获得积分
    public function actionGetScore()
    {
        $lib = new UserWapLib();
        $res = $lib->getMyScore($_POST);
        HttpUtil::out($res);
    }

    //积分兑换秒杀机会
    public function actionGetExchangeScore()
    {
        $lib = new UserWapLib();
        $res = $lib->ExchangeScore($_POST);
        HttpUtil::out($res);
    }

    //获取我的积分商品
    public function actionGetPointsGoods()
    {
        $lib = new UserWapLib();
        $res = $lib->getUserPoints($_POST);
        HttpUtil::out($res);
    }

    //资格券规则
    public function actionPointsRules()
    {
        $this->tpl->display('user/m/rules_qualifications.html');
    }

    //积分规则
    public function actionScoreRules()
    {
        $city = StringUtil::getCustomCity();
        $city = $city == 'wuhan' ? 'WH' : 'NJ';
        $res = MarketSettingVo::model()->find('city = :city', array(':city' => $city));
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/rules_score.html');
    }

    //账户使用说明
    public function actionUserInstr()
    {
        $this->tpl->display('user/m/user_instr.html');
    }

    /*编辑地址*/
    public function actionEditInfo()
    {
        $lib      = new UserAssociateLib();
        $id       = intval($_GET['id']);
        $in       = array(
            'id' => $_GET['id'],
        );
        $res_temp = $lib->getAllUserAssociate($in);
        $res      = $res_temp['data'];

        foreach ((array)$res as $key => $val) {
            $u_id = $val['id'];
            if ($u_id == $id) {
                $res = $val;
                break;
            }
        }

        $res['info_id'] = $_GET['info_id'];
        $res['coupon_id'] = $_GET['coupon_id'];
        $res['id'] = $_GET['id'];

        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/edit_info.html');
    }

    //选择收货地址
    public function actionSelectAddr()
    {
        $this->tpl->display('user/m/user_select_addr.html');
    }

    //账户余额 ok
    public function actionRemainAccount()
    {
        $user = new UserWapLib();
        $res_temp  = $user->getUserAccount(null);
        $res = $res_temp['data'];
        // 判断是否需要分享
        $data = $_GET;
        if ($data['share'] == 1){
            // 分享参数
            $res_temp = ShareLib::payShare();
            $share_res = $res_temp['data'];

            // 绑定套餐数据 是否提供微信分享
            $user_vo = UserLib::getNowUser();
            $user_id = intval($user_vo['uid']);

            //vip抽奖活动
            $lottery_id = LotteryService::getVipLotteryId();
            $service_share = new ShareConfigService($lottery_id);

            // 会员套餐数据
            $vip_session_temp = MemberLib::getFreeCoupon();
            $vip_session = $vip_session_temp['data'];
            $total = $data['total'];

            if($vip_session && $total >= $vip_session[0]['price']){
                // 刚刚成为vip 分享之后不需要跳转
                $wechat_share = $service_share->getShareConfigForVip($user_id, 0);
                $res['package_info'] = UserService::getVipChargeNotice($vip_session, $total);
                $is_show_first_vip = 1;
            }else{
                $is_show_first_vip = 0;
                $wechat_share = $service_share->getShareConfigForVip($user_id, 1);
            }

            // 用户充值金额超过最低额度套餐的时候显示
            $res['share'] = $share_res;
            $res['is_show_first_vip'] = $is_show_first_vip;
            $this->tpl->assign('wechat_share', json_encode($wechat_share));
            $this->tpl->assign('wechat_flag', $share_res['share']);
        }
        $res_member_temp = MemberLib::init();
        $res['show_my_wallet_vip'] = $res_member_temp['data']['show_my_wallet_vip'];

        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/vip_remain_account.html');
    }

    //获取账户花销详情
    public function actionGetAccountList()
    {
        $user = new UserWapLib();
        $res  = $user->getUserAccount($_POST);
        HttpUtil::out($res);
    }


    //秒杀资格 ok
    public function actionSeckill()
    {
        $lib                = new UserWapLib();
        $res                = $lib->getUserSeckill(null);
        $sid                = $_GET['id'];
        $res['data']['sid'] = $sid;
        $this->tpl->assign('res', $res['data']);
        $this->tpl->display('user/m/user_seckill.html');
    }

    //秒杀资格 获取资格商品
    public function actionGetSeckillCommodity()
    {
        $user = new UserWapLib();
        $res  = $user->getUserSeckill($_POST);
        HttpUtil::out($res);
    }


    //我的收藏
    public function actionUserCollect()
    {
        $this->tpl->display('user/m/user_collect.html');
    }

    //4.1我想去列表
    public function actionWantGo(){
        $lib = new UserWapLib();
        $kind = $_GET['type'];
        if($kind == null){//提醒
            $res = $lib->userRemindGo($_POST);
            $res['data'] = $res['data']['reminds'];
//            DevUtil::e($res);exit;
        }else if ($kind == 1){ //我想去
            $res = $lib->userWantGo($_POST);
            $res['data'] = $res['data']['want_goes'];
//            DevUtil::e($res);exit;
        }
        $res['kind'] = $kind;
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/user_want_go.html');
    }
    //想去场次提醒列表
    public function actionRemindList(){
        HttpUtil::out(UserWapLib::userRemindGo($_POST));
    }
    //全部想去时间列表
    public function actionAllWantList(){
        HttpUtil::out(UserWapLib::userWantGo($_POST));
    }
    //分销规则说明
    public function actionDistribution()
    {
        $this->tpl->display('user/m/distribution.html');
    }

    // 修改/设置支付密码 中转页面
    public function actionPasswordTransition()
    {
        // todo 这个页面判断用户手机号是否绑定
        $user_info_tmp = UserWapLib::getUserInfo();
        $user_info     = $user_info_tmp['data'];
        $res = $user_info;
        if ($user_info['phone']) {
            // 已绑定手机号
            $this->tpl->assign('res', $res);
            $this->tpl->display('user/m/password_transition.html');
        } else {
            // 未绑定手机号
            // todo 跳转到手机号绑定界面
        }
    }

    // 修改
    public function actionPasswordSet()
    {
        $data = $_GET;
        $user_vo      = UserLib::getNowUser();
        $info  = array(
            'code' => $data['code'],
            'flag' => $data['flag'],
            'phone'=> $user_vo['phone']
        );
        $res['flag'] = $data['flag'];
        $res['info'] = json_encode($info);

        // 修改的流程是输入一次旧密码 在输入一次新密码
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/password_set.html');
    }

    // 找回支付密码
    public function actionPasswordBack()
    {
        $user_vo      = UserLib::getNowUser();
        $res['phone'] = $user_vo['phone'];
        $res['type'] = $_GET['type'];
        $res['flag'] = $_GET['flag'];
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/password_back.html');
    }

    // 给用户发送验证码
    public function actionGetCode()
    {
        HttpUtil::out(UserWapLib::getCode($_POST));
    }

    // 验证密码
    public function actionVerifyPassword()
    {
        HttpUtil::out(UserWapLib::verifyPassword($_POST));
    }

    // 验证验证码
    public function actionVerifyCode()
    {
        $user_vo       = UserLib::getNowUser();
        $sms           = new SmsService();
        $flag          = $sms->checkCode($user_vo['phone'], $_POST['code'],$_POST['type']);
        $res           = HttpUtil::getHttpResult();
        $res['status'] = $flag;
        HttpUtil::out($res);
    }

    // 更新密码
    public function actionUpdatePassword()
    {
        HttpUtil::out(UserWapLib::updatePassword($_POST));
    }

    // 找回/重置 密码
    public function actionEditPassword()
    {
        HttpUtil::out(UserWapLib::editPassword($_POST));
    }

    // 手机验证
    public function actionRegister()
    {
        $this->tpl->display('user/m/register.html');
    }

    // 支付设置
    public function actionPaymentSet()
    {
        $this->tpl->display('user/m/payment_set.html');
    }

    public function actionGetAllUserCollect()
    {
        HttpUtil::out(UserWapLib::userCollect($_POST));
    }

    /*地址列表*/
    public function actionAddressList()
    {
        $refer = $_SERVER['HTTP_REFERER'];

        $source_arr = array(
            'user/center',
            'play/playSeleApplic',
            'ticket/commodityselect',
        );

        foreach($source_arr as $key => $val){
            if(strstr(strtolower($refer), strtolower($val))){
                $_SESSION['address_refer'] = $refer;
                break;
            }
        }

        $res = TicketLib::addressList();
        $this->tpl->assign('res', $res['data']);
        $this->tpl->assign('address_refer', $_SESSION['address_refer']);
        $this->tpl->display('user/m/address_list.html');
    }

    //我的消息
    public function actionMyMessage()
    {
        if (isset($_POST) && $_POST != null){
            HttpUtil::out(UserWapLib::userMessage($_POST));
        }else{

            $res_temp = UserWapLib::userMessage(array('message_id' => 0));
            $res = $res_temp['data'];
            $size = count($res['message_list'])-1;
            $res['message_id'] = $res['message_list'][$size]['id'];
            $this->tpl->assign('res', $res);
            $this->tpl->display('user/m/user_message.html');
        }
    }

    //储值卡
    public function actionRechargeCard(){
        $this->tpl->display('user/m/recharge_card.html');
    }

    //存储卡表单验证
    public function actionRechargeCardPost()
    {
        HttpUtil::out(UserWapLib::rechargeCard($_POST));
    }
}