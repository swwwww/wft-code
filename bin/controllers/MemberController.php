<?php
/**
 * Created by IntelliJ IDEA.
 * User: asus
 * Date: 2016/11/7
 * Time: 23:02
 */

class MemberController extends Controller
{

    public function filters()
    {
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    public function actionRank(){
        $sql = "select pu.uid, pu.img, pu.username, pu.phone, pm.member_share_recharge_count from play_member as pm
                left join play_user as pu
                on pu.uid = pm.member_user_id
                where pm.member_share_recharge_count > 0
                order by pm.member_share_recharge_count desc";
        $all_rank = Yii::app()->db->createCommand($sql)->queryAll();

        $service = new MemberService();

        $result = array();
        $inner = intval($_GET['inner']);
        foreach((array)$all_rank as $key => $val){
            $user_id = $val['uid'];
            if($inner == 1){
                $val['phone'] = StringUtil::processPhone($val['phone']);
                $result[] = $val;
            }else{
                if($service->checkInnerPlayer($user_id) == false){
                    $val['phone'] = StringUtil::processPhone($val['phone']);
                    $result[] = $val;
                }
            }
        }

        $this->tpl->assign('rank_list', $result);
        $this->tpl->display('member/m/rank.html');
    }

    public function actionGuide(){
        $data = $_GET;

        $target_date = '2016-11-28 10:00:00'; /*2016-11-28 上午十点*/
        $start_time = strtotime($target_date);

        $target_end_date = '2017-01-03 23:59:00'; /*2016-01-03 晚上十二点*/
        $end_time = strtotime($target_end_date);
        $left_end_time = $end_time - time();
        $left_time = $start_time - time();


        $user_vo = UserLib::getNowUser();
        $user_id = intval($user_vo['uid']);

        $channel = $data['channel'];
        $channel = $channel == '' ? 'wft_wechat' : $channel;

        $share_user_id = intval($data['share_user_id']);

        $lottery_id = LotteryService::getVipLotteryId();//vip抽奖活动

        $service = new LotteryService($lottery_id);
        $login_record = $service->processLotteryLoginRecord($user_id, $channel);//记录渠道

        $service_share = new ShareConfigService($lottery_id);
        $wechat_share = $service_share->getShareConfigForVip($user_id, 1);
        $this->tpl->assign('wechat_share', json_encode($wechat_share));

        $now_date = TimeUtil::getNowDate();
        $vip_name = 'vip_ad_1128';

        $vip_pv = PageViewData::updatePv($vip_name, $channel);//记录各渠道pv

        //分销员入口
        if($now_date >= '2016-11-24'){
            $channel_arr = array();//array('wft_seller');
            if(in_array($channel, $channel_arr)){
                $_SESSION['vip_share_user_id'] = $share_user_id;
                $base_host = HttpUtil::getBaseHost();
                $url = $base_host . '/orderWap/chargeMoney?channel=wft_seller';
                JumpUtil::go($url);
            }
        }

        //抽奖页面
        if ($user_id != 0 && $share_user_id == $user_id){
            $win_service = new WinService($lottery_id);

            $result = array();
            $left_total = $win_service->getWinUserLeftTotal($user_id);

            if($left_total > 0){
                $page_total = $win_service->getUserShareClick($user_id);

                $result = array(
                        'lottery_id'       => $lottery_id,
                        'pv'               => $page_total,/*浏览pv*/
                        'op_total'         => $left_total,
                        'phone'            => $phone,
                );
                $this->tpl->assign('lottery_vo', $result);

                $this->tpl->display('cash/m/cash_index.html');
                Yii::app()->end();
            }
        }

        // 剩余时间大于0的时候到倒计时页面
        if ($left_time > 0){
            $this->tpl->assign('res', $left_time);
            $this->tpl->display('cash/m/count_down.html');
        }else{
            if($left_end_time > 0){
                // 两周年活动开始
                if($share_user_id > 0 && $user_id > 0){
                    $win_service = new WinService($lottery_id);
                    $win_service->updateUserShareClick($share_user_id, $user_id);
                }

                // vip充值 - 将uid写入缓存 在充值套餐的时候提供
                if($share_user_id > 0 && $share_user_id != $user_id){
                    $_SESSION['vip_share_user_id'] = $share_user_id;
                }

                $res['goods'] = MemberService::guidePageInfo();
                $res['vip_num'] = MemberService::getVipNum();
                $this->tpl->assign('res', $res);
                $this->tpl->assign('vip_share_user_id', $share_user_id);
                $this->tpl->display('member/m/guide_page.html');
            }else{
                $lib  = new RecommendLib();
                $data = array(
                    'page'     => 1,
                    'page_num' => 10,
                );
                $res  = $lib->choiceList($data);
                $this->tpl->assign('res', $res['data']);
                $this->tpl->display('recommend/m/index.html');
            }
        }
    }

    //会员专区
    public function actionSpecialArea()
    {
        $res = MemberLib::areaLists();
        //                DevUtil::e($res); exit();

        $this->tpl->assign('res', $res);
        $this->tpl->display('member/m/special_area.html');
    }

    //活动会员游玩地
    public function actionGetAreaLists(){
        HttpUtil::out(MemberLib::areaLists($_POST));
    }

    //我的免费玩亲子游
    public function actionKidsTravel()
    {
        $res_temp = CashLib::getFreeCoupon();
        $res = $res_temp['data'];

        $user_vo = UserLib::getNowUser();
        $user_id = intval($user_vo['uid']);
        $lottery_id = LotteryService::getVipLotteryId();//vip抽奖活动
        $service_share = new ShareConfigService($lottery_id);
        $wechat_share = $service_share->getShareConfigForVip($user_id, 0);
        $this->tpl->assign('wechat_share', json_encode($wechat_share));

        // vip 判断
        $res_user_temp = UserWapLib::getUserInfo();
        $res['is_vip'] = $res_user_temp['data']['is_vip'];

        //        DevUtil::e($res); exit();
        $this->tpl->assign('res', $res);
        $this->tpl->display('member/m/free_kids_travel.html');
    }

    //会员信息初始化
    public function actionInit(){
        HttpUtil::out(MemberLib::init());
    }

    public function actionLucky(){
        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );

        $lottery_id = LotteryService::getVipLotteryId();
        $service = new WinService($lottery_id);

        $user_vo = UserLib::getNowUser();
        if ($user_vo) {
            $user_id = intval($user_vo['uid']);

            if ($service->lottery_vo) {
                $allow_flag = false;

                $left_total = $service->getWinUserLeftTotal($user_id);

                if ($left_total > 0) {
                    $allow_flag = true;
                }

                if ($allow_flag) {
                    $lucky_result = $service->lucky($user_id);

                    if ($lucky_result) {
                        $result['status'] = 1;
                        $result['data'] = $lucky_result['money'];
                    } else {
                        $result['data'] = 100;
                        $result['msg']  = '没有中奖';
                    }
                } else {
                    $result['data'] = 101;
                    $result['msg']  = '抽奖机会已用完';
                }
            } else {
                $result['data'] = 103;
                $result['msg']  = '抽奖活动不存在';
            }
        } else {
            $result['data'] = 102;
            $result['msg']  = '请先登陆';
        }

        HttpUtil::out($result);
    }

    public function actionGetVipSession(){
        HttpUtil::out(MemberLib::getFreeCoupon());
    }

    public function actionGoodsMore(){
        $res['goods'] = MemberService::guideSecondPage();

        $user_vo = UserLib::getNowUser();
        $user_id = intval($user_vo['uid']);
        $lottery_id = LotteryService::getVipLotteryId();//vip抽奖活动
        $service_share = new ShareConfigService($lottery_id);
        $wechat_share = $service_share->getShareConfigForVip($user_id, 1);
        $this->tpl->assign('wechat_share', json_encode($wechat_share));

        $this->tpl->assign('vip_share_user_id', intval($_SESSION['vip_share_user_id']));

        $this->tpl->assign('res', $res);
        $this->tpl->display('member/m/goods_more.html');
    }

}