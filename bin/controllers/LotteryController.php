<?php

/**
 * 抽奖活动
 *
 * @classname: LotteryController
 * @author   11942518@qq.com | quenteen
 * @date     2016-6-29
 */
class LotteryController extends Controller
{
    /**
     * 抽奖活动落地页
     *
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-29 下午03:33:43
     */
    public function actionMarket()
    {
        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];
        $user_name = $user_vo['username'];
        $lottery_id    = intval($_GET['lottery_id']);
        $channel       = $_GET['channel'];
        $channel       = $channel == '' ? 'wft_wechat' : $channel;
        $luck          = intval($_GET['luck']);
        $share_user_id = intval($_GET['share_user_id']);
        $page_key = 'index';

        $service = new LotteryService($lottery_id);

        if ($service->lottery_vo) {
            $this->setTitle($service->lottery_vo['detail']);
            $left_total = $service->getLotteryUserLeftTotal($user_id);
            $page_total = PageViewData::updatePv($service->lottery_vo['name'], $channel);//记录各渠道pv

            $max_score = $service->getMaxScoreByUid($user_id);               //最好纪录

            $lottery_config = $service->getConfig();

            //分享链接被点击，则抽奖次数加1
            if ($share_user_id > 0) {
                $service->updateUserShareClick($share_user_id, $user_id);
                $_SESSION['share_user_id'] = $share_user_id;

            }

            //$special_cash      = $service->getTodaySpecialCash();
            //$all_superstar     = $service->getAllSuperstar();
            $all_product_arr   = $service->getAllLotteryProduct('common');
            //$other_product_arr = $service->getAllLotteryProduct('other');

//            $type        = 2;//只获取抽中的记录
//            $all_award   = $service->getLotteryUserRecord($user_id, $type);
//            $award_total = count($all_award);

            //排行
            //$rank_list      = LotteryData::getAllGodActForRank();
            //$newest_vote_id = $service->getVoteByUserId($user_id);

            //game
            $game_start_time = $lottery_config['start_time'];
            $game_end_time   = $lottery_config['end_time'];
            $now_time        = TimeUtil::getNowDateTime();
            $is_end          = $now_time >= $game_start_time && $now_time <= $game_end_time ? 'no' : 'yes';

            $login_record = $service->processLotteryLoginRecord($user_id, $channel);//记录渠道

            if ($luck == 1) {
                $page_key         = 'luck';
                $game_score_total = LotteryData::getLotteryScoreTotal($lottery_id, $user_id);
                $game_score_total = $game_score_total < 0 ? 0 : $game_score_total;
                $horseLamp = $service->horseRaceLamp($lottery_id);
                $phone = $user_vo['phone'];
                $subject = $service->getSubject();
            }
            $spend_score = $lottery_config['spend_score'];
            $score_record  = LotteryData::getAllLotteryScoreRecord($lottery_id, $user_id);
            $is_first_game = $score_record ? 'no' : 'yes';

            //双十一
            if ($lottery_id == 4) {
                $show_period     = $service->getLotteryShowPeriod($lottery_config['show_period']);
                $all_product_arr = $service->processLotteryProductForOnline($all_product_arr, $show_period);
                $now_date        = TimeUtil::getNowDate();

                $is_taobao_date = $now_date >= '2016-11-10' && $now_date <= '2016-11-11' ? true : false;
                $taobao_period  = $now_date == '2016-11-10' ? 'one' : 'two';
                if($_GET['taobao'] == 1){
                    $is_taobao_date = true;
                    $taobao_period = 'one';
                }
                if ($is_taobao_date) {
                    //$now_hour = date('H', strtotime('2016-10-11 09:12'));
                    $now_hour = intval(date('H'));

                    if ($now_hour >= 9 && $now_hour <= 11) {
                        $taobao_during = 9;
                    } else if ($now_hour >= 15 && $now_hour <= 17) {
                        $taobao_during = 16;
                    } else if ($now_hour >= 20 && $now_hour <= 22) {
                        $taobao_during = 21;
                    }

                    $kill_goods = array();
                    foreach ($all_product_arr['3'] as $key => $val) {
                        $v_period = $val['period'];
                        $v_during = $val['during'];

                        if ($v_period == $taobao_period) {
                            $kill_goods[$v_during][] = $val;
                        }
                    }

                    $all_product_arr['3'] = $kill_goods;
                }
            }

            $result = array(  //是否有奖品 积分 一次要多少分 游戏次数
                'lottery_id'       => $lottery_id,
                'pv'               => $page_total,/*浏览pv*/
                'op_total'         => $left_total,              //剩余次数
                'game_score_total' => $game_score_total,            //总积分
                'spend_score'      => $spend_score,             //单次抽奖需要分数
                'goods'            => $all_product_arr,
                'horse_lamp'       => $horseLamp,
                'max_score'        => $max_score,
                'award_total'      => intval($award_total),
                'other_goods'      => $other_product_arr,
                'special_cash'     => $special_cash,
                'all_superstar'    => $all_superstar,
                'rank_list'        => $rank_list,
                'newest_vote_id'   => $newest_vote_id,
                'is_first_game'    => $is_first_game,
                'is_end'           => $is_end,
                'phone'            => $phone,
                'login_record'     => $login_record,
                'subject'          => $subject,
                'taobao_sale'      => array(
                    'show_period'    => $show_period,
                    'now_date'       => $now_date,
                    'now_hour'       => $now_hour,
                    'is_taobao_date' => $is_taobao_date,
                    'taobao_period'  => $taobao_period,
                    'taobao_during'  => $taobao_during,
            ),
            );

            $share_config  = array(
                'user_id' => $user_id,
                'user_name' => $user_name,
                'vote_id' => 0,
            );

            $wechat_config = $service->getAppShareConfig($this,$share_config);

            $this->tpl->assign('lottery_vo', $result);
            $page_url = $lottery_config['page'][$page_key];

            $this->tpl->display($page_url);
        } else {
            $url = $this->createUrl('lottery/market/lottery_id/6');  //5
            $this->redirect($url);
        }
    }

    //几古家绑定手机
    public function actionBindJiguPhone(){
        $data = $_POST;

        $lottery_id = $data['lottery_id'];
        $phone = $data['phone'];

        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];

        $service = new LotteryService($lottery_id);
        $result = HttpUtil::getHttpResult();
        if ($service->lottery_vo) {
            $rst = $service->processLotteryLoginRecord($user_id, '', $phone);

            if($rst){
                $result['status'] = 1;
                $result['data'] = $phone;
            }
        }

        HttpUtil::out($result);
    }

    //保存猜字游戏结果
    public function actionGuessShop()
    {
        $result = HttpUtil::getHttpResult();

        $data       = $_POST;
        $lottery_id = intval($data['lottery_id']);
        $flag       = intval($data['result']);

        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];

        $service = new LotteryService($lottery_id);
        if ($service->lottery_vo) {
            $log_date = TimeUtil::getNowDate();
            //获取每天的猜题次数
            $login_vo = LotteryLoginRecordVo::model()->find("lottery_id = {$lottery_id} and user_id = {$user_id} and log_date = '{$log_date}'");

            if ($login_vo && $login_vo->guess_total >= 1) {
                $login_vo->guess_total = $login_vo->guess_total - 1;
                $login_vo->save();

                //答题正确，则抽奖次数加1
                if ($flag == 1) {
                    $total_limit = intval($service->lottery_vo['total_limit']);
                    $total_limit = $total_limit == 0 ? 20 : $total_limit;

                    //2.更新用户抽奖次数
                    $sql = "update ps_lottery_user_total
                            set total = total + 2
                            where log_date = '{$log_date}'
                            and lottery_id = {$lottery_id}
                            and user_id = {$user_id}
                            and total < {$total_limit}";

                    $update = Yii::app()->db->createCommand($sql)->execute();

                    $result['status'] = 1;
                }
            }
        }

        HttpUtil::out($result);
    }

    //抽奖
    public function actionAcceptLotteryRecord()
    {

        $result = HttpUtil::getHttpResult();

        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];

        $data       = $_POST;
        $lottery_id = intval($data['lottery_id']);
        $record_id  = intval($data['record_id']);

        $flag = false;

        if ($user_vo['phone']) {//已经有了电话号码，无需重新绑定
            $flag = true;
        } else {
            $auth_service = new AuthService();


            $auth_result  = $auth_service->submitPhoneLogin($data, $lottery_id);

//            $flag = $auth_result['status'] == 1 ? true : false;
            $flag = $auth_result['status'] == 1;

            //说明登陆用户已重新绑定了新手机号，需要做用户信息变更
            if ($flag) {

                $new_user_id = $auth_result['data']['new_user_id'];

                LogUtil::trace('[LotterController][acceptLotteryRecord](change_user_id): ' . json_encode($auth_result));

                //统一变更中奖记录
                $sql = "update ps_lottery_user_record
                        set user_id = {$new_user_id}
                        where lottery_id = {$lottery_id}
                        and user_id = {$user_id}";

                $update_res = Yii::app()->db->createCommand($sql)->execute();

                //更新为最终的正确用户
                $user_vo = UserData::getUserById($new_user_id);
                UserLib::setNowUser($user_vo);
            }
        }

        if ($flag) {
            $service = new LotteryService($lottery_id);

            if ($service->lottery_vo) {

                $accept_result = $service->acceptLotteryRecord($record_id, $user_vo);
                if ($accept_result['status'] == 1) {
                    $result = $accept_result;
                } else {
                    $result['msg'] = $accept_result['msg'];
                }
            }
        } else {
            $result['msg'] = $auth_result['msg'];
        }

        HttpUtil::out($result);
    }
//我抽中的奖品
    public function actionRecord()
    {
        $user_vo = UserLib::getNowUser();
        $user_id = intval($user_vo['uid']);
        $user_name = $user_vo['username'];

        $lottery_id = intval($_GET['lottery_id']);

        $service = new LotteryService($lottery_id);
        if ($service->lottery_vo) {
            $this->setTitle($service->lottery_vo['detail']);
            $left_total = $service->getLotteryUserLeftTotal($user_id);

            $lottery_config = $service->getConfig();

            $type              = 2;//只获取抽中的记录
            $award_list        = $service->getLotteryUserRecord($user_id, $type);
            $other_product_arr = $service->getAllLotteryProduct('other');

            $newest_vote_id = $service->getVoteByUserId($user_id);

            $game_score_total = LotteryData::getLotteryScoreTotal($lottery_id, $user_id);
            $game_score_total = $game_score_total < 0 ? 0 : $game_score_total;


            $phone = $user_vo['phone'];

            $result = array(           //todo理解
                'phone'          => $phone,
                'lottery_id'     => $lottery_id,
                'left_total'     => $left_total,
                'award_list'     => $award_list,
                'game_score_total'     => $game_score_total,
                'other_goods'    => $other_product_arr,
                'newest_vote_id' => $newest_vote_id,
            );

            $share_config  = array(
                'user_id' => $user_id,
                'user_name' => $user_name,
                'vote_id' => 0,
            );
            $wechat_config = $service->getAppShareConfig($this, $share_config);

            $this->tpl->assign('award_vo', $result);

            $page_url = $lottery_config['page']['record'];
            $this->tpl->display($page_url);
        } else {
            $url = Yii::app()->createUrl("lottery/record/lottery_id/1");
            Yii::app()->request->redirect($url);
        }
    }

    /**
     * 抽奖动作
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-29 下午02:13:38
     */
    public function actionLucky()
    {
        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );

        $lottery_id = intval($_POST['lottery_id']);
        $service    = new LotteryService($lottery_id);

        $lottery_detail = $service->LOTTERY_CONFIG[$lottery_id];

        //活动到期
        $now = TimeUtil::getNowDateTime();
        if ($now >= $lottery_detail['end_time']) {
            $result['msg'] = '抽奖活动已结束噢';
            HttpUtil::out($result);
        }

        $user_vo = UserLib::getNowUser();
        if ($user_vo) {
            $user_id = intval($user_vo['uid']);

            if ($service->lottery_vo) {
                $allow_flag = false;

                if ($lottery_id == 3 || $lottery_id == 6) {//针对积分抽奖
                    $score_limit      = $lottery_detail['spend_score'];
                    $game_score_total = LotteryData::getLotteryScoreTotal($lottery_id, $user_id);

                    if ($game_score_total >= $score_limit) {
                        $allow_flag = true;
                    }
                } else {
                    $left_total = $service->getLotteryUserLeftTotal($user_id);

                    if ($left_total > 0) {
                        $allow_flag = true;
                    }
                }

                if ($allow_flag) {
                    $lottery_type = $lottery_detail['type'];

                    $lucky_result = $service->lucky($user_id, $lottery_type);

                    if ($lucky_result) {
                        $result['status'] = 1;
                        $result['data']   = $lucky_result;
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

    public function actionFriend()
    {
        $lottery_id = intval($_GET['lottery_id']);
        $vote_id    = intval($_GET['vote_id']);
        $user_id    = intval($_GET['share_user_id']);

        $wft_param = $_REQUEST['p'];

        $service = new LotteryService($lottery_id);
        $vote_vo = $service->getVoteDetailByVoteId($vote_id, $lottery_id);    //男神投票

        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];

        if ($service->lottery_vo && $vote_vo) {
            $this->setTitle($service->lottery_vo['detail']);
            $left_total = $service->getLotteryUserLeftTotal($user_id);

            $share_user_id = $vote_vo['user_id'];
            //分享链接被点击，则抽奖次数加1
            if ($user_id > 0) {
                $service->updateUserShareClick($share_user_id, $user_id);
                $_SESSION['share_user_id'] = $share_user_id;
            }

            $star_vo           = LotterySuperstarVo::model()->findByPk($vote_vo['star_id']);
            $star_vo           = $star_vo->getAttributes();
            $other_product_arr = $service->getAllLotteryProduct('other');

            $type                = 2;//只获取抽中的记录
            $all_award           = $service->getLotteryUserRecord($user_id, $type);
            $award_total         = count($all_award);
            $undoing_award_total = $service->getLotteryUserRecordUndoingTotal($user_id, $type);

            $is_my_friend_group = $vote_vo['user_id'] == $user_id ? true : false;

            $result = array(
                'lottery_id'          => $lottery_id,
                'op_total'            => $left_total,
                'other_goods'         => $other_product_arr,
                'award_total'         => intval($award_total),
                'undoing_award_total' => $undoing_award_total,
                'vote_vo'             => $vote_vo,
                'star_vo'             => $star_vo,
                'is_my_friend_group'  => $is_my_friend_group,
            );

            $this->tpl->assign('lottery_vo', $result);
            $this->tpl->assign('wft_param', $wft_param);

            $share_config  = array(
                'user_id' => $user_id,
                'vote_id' => $vote_id,
            );
            $wechat_config = $service->getAppShareConfig($this, $share_config);

            $this->tpl->display('ad/god/friend_list.html');
        } else {
            $url = $this->createUrl("lottery/market/lottery_id/{$lottery_id}");
            $this->redirect($url);
        }
    }

    /**
     * 游戏界面
     * @author 11942518@qq.com | quenteen
     * @date   2016-10-29 下午04:44:31
     */
    public function actionGame()
    {
        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];
        $lottery_id = intval($_GET['lottery_id']);
        $service = new LotteryService($lottery_id);
        $user_name = $user_vo['username'];

        if ($service->lottery_vo) {
            $this->setTitle($service->lottery_vo['detail']);
            $left_total = $service->getLotteryUserLeftTotal($user_id);

            $lottery_config = $service->getConfig();

            $game_start_time = $lottery_config['start_time'];
            $game_end_time   = $lottery_config['end_time'];
            $now_time        = TimeUtil::getNowDateTime();
            $is_end          = $now_time >= $game_start_time && $now_time <= $game_end_time ? 'no' : 'yes';

            $spend_score = $lottery_config['spend_score'];
            $sum_score = LotteryData::getLotteryScoreTotal($lottery_id, $user_id);          //调用方法计算总分

            $score_record  = LotteryData::getAllLotteryScoreRecord($lottery_id, $user_id);
            $score_total   = LotteryData::getLotteryScoreTotal($lottery_id, $user_id);
            $is_first_game = count($score_record) >= 2 ? 'no' : 'yes';

            $result = array(
                'lottery_id'    => $lottery_id,
                'op_total'      => $left_total,
                'is_first_game' => $is_first_game,
                'is_end'        => $is_end,
                'score_total' => $sum_score,     //总分
                'spend_score' => $spend_score,   //抽奖花费     备案  传计算前总分已解决实时弹窗
            );

            $this->tpl->assign('lottery_vo', $result);

            $share_config  = array(
                'user_id' => $user_id,
                'user_name' => $user_name,
                'vote_id' => 0,
            );
            $wechat_config = $service->getAppShareConfig($this, $share_config);


            $page_url = $lottery_config['page']['game'];
            $this->tpl->display($page_url);
        } else {
            $url = $this->createUrl('lottery/market/lottery_id/6');
            $this->redirect($url);
        }
    }

    /**
     * 游戏得分接口
     * @author 11942518@qq.com | quenteen
     * @date   2016-10-29 下午04:44:42
     */
    public function actionGameScore()
    {
        $user_vo = UserLib::getNowUser();
        $user_id = intval($user_vo['uid']);

        $data       = $_POST;
        $lottery_id = intval($data['lottery_id']);
        $score      = intval($data['score']);

        $result = HttpUtil::getHttpResult();

        $service = new LotteryService($lottery_id);

        if ($service->lottery_vo) {
            $now           = TimeUtil::getNowDate();
            $user_total_vo = LotteryUserTotalVo::model()->find("log_date = '{$now}' and lottery_id = {$lottery_id} and user_id = {$user_id}");

            $op_total = $user_total_vo['op_total'];

            $score_total = LotteryData::getLotteryScoreRecordTotal($lottery_id, $user_id, $now);

            //防作弊如果游戏获得积分次数 小于 玩游戏次数，则可以正常获取积分
            if ($score_total <= $op_total && $score <= 240) {
                $score_vo             = new LotteryScoreRecordVo();
                $score_vo->log_date   = $now;
                $score_vo->lottery_id = $lottery_id;
                $score_vo->user_id    = $user_id;
                $score_vo->type       = 'game';
                $score_vo->score      = $score;
                $score_vo->created    = TimeUtil::getNowDateTime();
                $score_vo->updated    = TimeUtil::getNowDateTime();

                $score_vo->save();
                $result['status'] = 1;

                $sum_score = LotteryData::getLotteryScoreTotal($lottery_id, $user_id);          //存储后 调用方法计算总分
                $result['data'] = array(
                    'score_total' => $sum_score,
                );

            } else {
                $result['msg'] = '您的网络开小差了，请刷新后重试';
            }
        } else {
            $result['msg'] = '参数错误，请刷新重试';
        }

        HttpUtil::out($result);
    }

    //投票接口
    public function actionVoteSuperstar()
    {
        $result = HttpUtil::getHttpResult();

        $lottery_id = intval($_POST['lottery_id']);
        $star_id    = intval($_POST['star_id']);

        $user_vo = UserLib::getNowUser();

        if (!$user_vo) {
            $result['msg'] = '请您先登陆，再来选男神';
            HttpUtil::out($result);
        }

        $service     = new LotteryService($lottery_id);
        $vote_result = $service->voteSuperstar($star_id);
        if ($vote_result) {
            $result['status'] = 1;
            $result['data']   = $vote_result;
        }

        HttpUtil::out($result);
    }

    //活动排行
    public function actionGodRank()
    {
        $lottery_id = intval($_GET['lottery_id']);
        $service    = new LotteryService($lottery_id);

        if ($service->lottery_vo) {
            //排行
            $rank_list = LotteryData::getAllGodActForRank();

            $result = array(
                'rank_list' => $rank_list,
            );

            $this->setTitle($service->lottery_vo['detail']);

            $user_vo = UserLib::getNowUser();
            $user_id = intval($user_vo['uid']);

            $newest_vote_id = $service->getVoteByUserId($user_id);
        }

        $share_config  = array(
            'user_id' => $user_id,
            'vote_id' => 0,
        );
        $wechat_config = $service->getAppShareConfig($this, $share_config);

        $this->tpl->assign('rank_list', $result);

        $this->tpl->display('ad/god/rank.html');
    }

    public function actionLogin()
    {
        $lottery_id = intval($_GET['lottery_id']);

        $share_user_id = intval($_SESSION['share_user_id']);
        $share_param   = $share_user_id ? '/share_user_id/' . $share_user_id : '';

        $url = $this->createUrl("lottery/market/lottery_id/{$lottery_id}" . $share_param);
        $this->redirect($url);
    }

    public function setTitle($title)
    {
        $platform = HttpUtil::getPlatform();
        if ($platform['wft']) {
            $this->tpl->assign('title', $title);
        }
    }

    /**
     * 游戏开始减少次数接口
     * @author rzfeng@wanfantian.com | rzfeng
     * @date   2017-01-11 19:52
     */
    public function  actionGameBegin(){

        $user_vo = UserLib::getNowUser();
        $user_id = $user_vo['uid'];
        $lottery_id = intval($_POST['lottery_id']);

        $service = new LotteryService($lottery_id);
        $lottery_config = $service->getConfig();

        $result = HttpUtil::getHttpResult();
        $left_total = $service->getLotteryUserLeftTotal($user_id);          //当前剩余游戏次数

            if ($left_total > 0) {
                //更新用户已抽奖次数
                $log_date = TimeUtil::getNowDate();
                $sql      = "update ps_lottery_user_total
                        set op_total = op_total + 1
                        where log_date = '{$log_date}'
                        and lottery_id = {$lottery_id}
                        and user_id = {$user_id}";

                $update     = Yii::app()->db->createCommand($sql)->execute();
                $left_total = $left_total - 1;

                $result['status'] = 1;
                $result['data'] = array(
                    'left_total' => $left_total,     //机会次数
                );
            }else{
                $result['status'] = 0;
                $result['msg'] = "游戏次数用完啦";
                $result['data'] = array(
                    'left_total' => -1,     //机会次数
                );

            }

        HttpUtil::out($result);
    }

    /**
     * 领奖弹窗
     * @author rzfeng@wanfantian.com | rzfeng
     * @date   2017-01-19 14：23
     */
    public function  actionDetail()  //lottery_cash_id
    {
        $user_vo = UserLib::getNowUser();
        $user_id = intval($user_vo['uid']);
        $user_name = $user_vo['username'];


            $lottery_id    = intval($_GET['lottery_id']);

            $record_id = intval($_GET['record_id']);

            $service = new LotteryService($lottery_id);
            $lottery_config = $service->getConfig();


            $record_vo = LotteryUserRecordVo::model()->find("id = {$record_id}");
            $cash_id = $record_vo['lottery_cash_id'];
            $cash_vo = LotteryCashVo::model()->find("id = {$cash_id}");

            $result['cash_name'] = $cash_vo['cash_name'];
            $result['type'] = $cash_vo['type'];

        $share_config  = array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'vote_id' => 0,
        );
            $wechat_config = $service->getAppShareConfig($this, $share_config);

            $page_key = 'prise';
            $page_url = $lottery_config['page'][$page_key];
            $this->tpl->assign('cash_vo', $result);
            $this->tpl->display($page_url);
        }
}
