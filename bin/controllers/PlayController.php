<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/13
 * Time: 16:13
 */
class PlayController extends Controller{

    public function filters(){
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    // 活动列表(首页)获取 OK
    public function actionPlayIndex()
    {
        $lib = new PlayLib();
        $sort = $_GET['sort'];
        $in = array(
            'sort' => $sort
        );
        $res_temp = $lib->playList($in);
        $res_list = $res_temp['data'];
        for ($i=0; $i<count($res_list); $i++){
            $res_list[$i]['sort'] = $sort;
        }
        $res_list['sort'] = $sort;

        $this->tpl->assign('res', $res_list);
        $this->tpl->display('play/m/play_index.html');
    }

    // 活动详情获取
    // 在调用接口的时候 uid 和 token 不需要传
    public function actionPlayActivity()
    {
        $id = intval($_GET['id']);
        $seller_id = intval($_GET['seller_id']);

        if($seller_id){
            $user_vo = UserLib::getNowUser();
            if($user_vo){
                $user_id = $user_vo['uid'];
                $sell_param = array(
                    'uid' => $user_id,
                    'seller_id' => $seller_id,
                    'id' => $id,
                    'type' => 'activity',
                );

                SellLib::shareClick($sell_param);
            }
        }

        $lib = new PlayLib();
        $res_temp = $lib->playInfo(array('id'=>$id));
        $res = $res_temp['data'];

        $res['player_level_img'] = $res['player_level'];
        $lv = substr($res['player_level'], -5, -4);
        if ($lv == 6) {
            $res['player_level'] = "黄金溜娃师";
        } else if ($lv == 5) {
            $res['player_level'] = "白金遛娃师";
        } else if ($lv == 4) {
            $res['player_level'] = "金牌遛娃师";
        } else if ($lv == 3) {
            $res['player_level'] = "遛娃师(配教)";
        }
        //数据库的操作查询，依据源码
        $information=ExerciseBaseVo::model()->find('id = :id', array(':id'=>$res['id']));
        $res['information'] = htmlspecialchars_decode($information['highlights']);

        $schedule = $res['itinerary'];

        foreach ((array)$schedule as $i => $row ){
            if (date('H', $row['dateline']) < 12){
                $content[$row['day']]['am'][] = array('date_line'=>date('H:i', $row['dateline']), 'end_dateline'=>date('H:i', $row['end_dateline']), 'content'=>$row['content']);
            }else{
                $content[$row['day']]['pm'][] = array('date_line'=>date('H:i', $row['dateline']), 'end_dateline'=>date('H:i', $row['end_dateline']), 'content'=>$row['content']);
            }

            $res['schedule'] = $content;
        }

        $custom_service_data = MemberLib::init();
        $res['custom_service'] = $custom_service_data['data'];
        //客服数据
        $res['service_info'] = MemberService::postServiceInfo($res, 2, $res['id']);



        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_activity.html');
    }
    //活动详情
    public function actionGetAllPlaces(){
        $lib = new PlayLib();
        httpUtil::out($lib->playInfo($_POST));
    }

    //活动成员
    public function actionPlayMember(){
        $lib = new PlayLib();
        $in = array(
            'id' => $_GET['id']
        );
        $res = $lib->playInfo($in);

        $this->tpl->assign('res', $res['data']);
        $this->tpl->display('play/m/play_member.html');
    }

    //咨询
    public function actionPlayConsult()
    {
        $lib = new PlayLib();
        $in = array(
            'play_id' => $_GET['id'],
        );
        $res_temp = $lib->playConsultLists($in);
        $res = $res_temp['data'];

        $res['play_id'] = $_GET['id'];
        //        DevUtil::e($res);exit();
        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_consult.html');
    }
    public function actionGiveConsult(){
        $lib = new PlayLib();
        httpUtil::out($lib->playConsult($_POST)); //咨询
    }
    public function actionGetConsultLists(){
        $lib = new PlayLib();
        httpUtil::out($lib->playConsultLists($_POST)); //咨询列表
    }

    //活动定制
    public function actionPrivateParty()
    {
        $lib = new PlayLib();
        $res_temp = $lib->privateParty();
        $res = $res_temp;

        $user_info_tmp = UserWapLib::getUserInfo();
        $user_info     = $user_info_tmp['data'];
        $res['info'] = $user_info;

        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_private_party.html');
    }

    //5. 活动订单信息及人数选择
    public function actionPlaySeleApplic(){
        $lib = new PlayLib();
        $lib_addr = new TicketLib();
        $data = $_GET;
        $in = array(
            'session_id' => $data['sid'],
        );

        $res_temp = $lib->playSeleApplic($in);
        $res = $res_temp['data'];

        $res_travel = PlayLib::playGetTravellers();
        $res_addr = $lib_addr->addressList($in);


        $res['info_id'] = $data['sid'];
        $res['coupon_id'] = $data['id'];

        $my_free_coupon_number = $res['my_free_coupon_number'];
        $most_free_buy_same_day_number = $res['most_free_buy_same_day_number'];
        // 会员购买资格的判断
        $res = MemberService::getPlaySelectVipShow($res, $most_free_buy_same_day_number, $my_free_coupon_number);

        $res['is_vip_banner'] = $my_free_coupon_number>0 ? 0:1;

        $this->tpl->assign('res_addr',$res_addr['data']); /*选择收货地址*/
        $this->tpl->assign('res_travel', $res_travel['data']); /*选择出行人*/
        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_select_applicants.html');
    }

    //集合地址
    public function actionPlayAddrLists(){
        $lib = new PlayLib();
        httpUtil::out($lib->playSeleApplic($_POST));
    }
    //集合地址
    public function actionGetTravellers(){
        httpUtil::out(PlayLib::playGetTravellers());
    }

    //6.遛娃活动场次选择
    public function actionPlayChoiceField(){
        $lib = new PlayLib();
        $in = array(
            'id' => $_GET['id']
        );
        $res_temp = $lib->playChoiceField($in);

        $res = $res_temp['data'];
        for($i=0; $i<count($res); $i++){
            $res[$i]['flag'] = $res[$i]['status'];
            if( $res[$i]['flag'] == 0){
                $res[$i]['status'] = "已结束";
            }elseif($res[$i]['flag'] == 1){
                $res[$i]['status'] = "报名中";
            }elseif ($res[$i]['flag'] == 2){
                $res[$i]['status'] = "已满员";
            }elseif ($res[$i]['flag'] == 3){
                $res[$i]['status'] = "未开始";
            }
            $res[$i]['info_id'] = $res[$i]['id'];
            $res[$i]['coupon_id'] = $_GET['id'];
        }

        //                DevUtil::e($res); exit();
        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_choice_field.html');
    }

    //获取及选择出行人
    public function actionPlaySelectTraveller(){
        $res_temp = PlayLib::playGetTravellers();
        $res['travellers'] = $res_temp['data'];
        $res['order_sn'] = $_GET['order_sn'];

        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_select_traveller.html');
    }

    //获取出行人列表
    public function actionGetTravellerList(){
        HttpUtil::out(PlayLib::playGetTravellers());
    }

    //添加出行人
    public function actionPlayAddTraveller()
    {
        $lib = new PlayLib();
        $in = array(
            'uid' => $_GET['uid'],
        );
        $res = $lib->playAddTraveller($in);

        $this->tpl->assign('res', $res['data']);
        $this->tpl->display('play/m/play_edit_attend.html');
    }
    public function actionAddTraveller(){
        $lib = new PlayLib();
        HttpUtil::out($lib->playAddTraveller($_POST));
    }
    //编辑出行人
    public function actionPlayEditTraveller()
    {
        $lib = new UserAssociateLib();
        $id = intval($_GET['id']);
        $in = array(
            'associates_id' => $_GET['id'],
        );
        $res_temp = $lib->getAllTravellerAssociate($in);
        $res = $res_temp['data'];

        foreach((array)$res as $key => $val){
            $u_id = $val['associates_id'];
            if($u_id == $id){
                $res = $val;
                break;
            }
        }

        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_edit_traveler.html');
    }

    public function actionEditTraveller(){
        $lib = new PlayLib();
        HttpUtil::out($lib->playEditTraveller($_POST)); //编辑出行人
    }

    public function actionDelTraveller(){
        $lib = new PlayLib();
        HttpUtil::out($lib->playDelTraveller($_POST)); //删除出行人
    }

    public function actionGetAllPlay()
    {
        $lib = new PlayLib();
        // todo 优化接收参数的方式

        HttpUtil::out($lib->playList($_POST));
        HttpUtil::out($lib->playSeleApplic($_POST));
        HttpUtil::out($lib->playChoiceField($_POST)); // 活动场次选择
    }

    public function actionPostPrivateParty(){
        // 专场活动定制
        $user_vo = UserLib::getNowUser();
        $data    = $_POST;

        $uid       = $user_vo['uid'];
        $name      = $data['name'];
        $phone     = $data['phone'];
        $num       = intval($data['num']);
        $coupon_id = intval($data['coupon_id']);

        $res = HttpUtil::getHttpResult();

        if(!$uid or !$name or !$phone or !$num){
            $res['status'] = 0;
            $res['msg']    = '参数错误';
            HttpUtil::out($res);
        }else if(!is_numeric($phone) or strlen($phone)!==11){
            $res['status'] = 0;
            $res['msg']    = '手机号码格式错误';
            HttpUtil::out($res);
        }

        if ($coupon_id){
            $private_party_vo = PrivatePartyVo::model()->find('uid = :uid' and 'coupon_id =: coupon_id', array(":uid"=>$uid, ":coupon_id"=>$coupon_id));
            if ($private_party_vo){
                $res['status'] = 0;
                $res['msg']    = '您已经提交过了';
                HttpUtil::out($res);
            }
        }

        unset($private_party_vo);
        $private_party_vo = new PrivatePartyVo();
        $private_party_vo->uid         = $uid;
        $private_party_vo->name        = $name;
        $private_party_vo->phone       = $phone;
        $private_party_vo->coupon_id   = $coupon_id;
        $private_party_vo->dateline    = time();
        $private_party_vo->join_number = $num;
        $status = $private_party_vo->save();
        if ($status){
            $res['status'] = 1;
            $res['msg']    = '提交成功!';
            HttpUtil::out($res);
        }else{
            $res['status'] = 0;
            $res['msg']    = '提交失败!';
            HttpUtil::out($res);
        }
    }

    //4.1 gathering what time want to go
    public function actionGatherDate(){
        $lib = new PlayLib();
        $res['play_id'] = $_GET['id'];

        $refer = $_SERVER['HTTP_REFERER'];
        $source_arr = array(
            'play/playActivity',
            'play/playChoiceField'
            );

            foreach ($source_arr as $key => $val){
                if(strstr(strtolower($refer), strtolower($val))){
                    $_SESSION['address_refer'] = $refer;
                    break;
                }
            }
            //
            $this->tpl->assign('res', $res);
            $this->tpl->assign('address_refer', $_SESSION['address_refer']);
            $this->tpl->display('play/m/play_gather_date.html');
    }
    public function actionGoDateSubmit(){
        HttpUtil::out(PlayLib::wantGoSubmit($_POST));
    }
    //想去日期列表
    public function actionWantGoList(){
        HttpUtil::out(PlayLib::wantGoList($_POST));
    }

    public function actionBackPay()
    {
        HttpUtil::out(PlayLib::backPay($_POST));
    }

    public function actionMakeParty()
    {
        $lib = new PlayLib();
        HttpUtil::out($lib->makeParty($_POST));
    }

}