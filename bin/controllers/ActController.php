<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/13
 * Time: 15:55
 */
class ActController extends Controller
{
    private $new_sum = 800;
    private $old_sum = 1200;
    private $end_time = 1472227199;

    public function actionCash(){
        $act_id = 1;
        $this->tpl->assign('title', '【玩翻天】扫码有好礼带你玩翻天！');
        $this->tpl->assign('act_id', $act_id);

        $this->tpl->display('ad/cash/cash_index.html');
    }

    public function actionCashResult(){
        $this->tpl->assign('title', '【玩翻天】扫码有好礼带你玩翻天！');

        $this->tpl->display('ad/cash/cash_result.html');
    }

    public function actionCashForCode(){
        $result = HttpUtil::getHttpResult();

        $phone = $_POST['phone'];
        $act_id = 1;
        $new_flag = 0;

        if(StringUtil::checkPhone($phone)){
            $act_record = ActUserRecordVo::model()->find('bind_phone = :bind_phone and act_id = :act_id and status = 1', array(
                ':bind_phone' => $phone,
                ':act_id' => $act_id,
            ));

            if(!$act_record){//没有兑换过现金券
                $ps_user_vo = UserData::getUserByPhone($phone);

                $ps_user_id = 0;
                if(!$ps_user_vo){
                    //1.保存play_user
                    $ps_user_vo = new UserVo();
                    $ps_user_vo->phone = $phone;
                    $ps_user_vo->token = md5(md5($phone) . time() . rand(1, 90000));
                    $ps_user_vo->login_type = 'weixin_wap';
                    $ps_user_vo->is_online = 1;
                    $ps_user_vo->dateline = time();
                    $ps_user_vo->status = 1;
                    $ps_user_vo->city = 'WH';
                    $ps_user_vo->save();

                    $new_flag = 1;
                }

                //2.play_user的uid
                $ps_user_id = $ps_user_vo['uid'];
                $ps_user_token = $ps_user_vo['token'];

                if($ps_user_id){
                    $gift_user_record = new ActUserRecordVo();

                    $gift_user_record->log_date = TimeUtil::getNowDateTime();
                    $gift_user_record->act_id = $act_id;
                    $gift_user_record->user_id = $ps_user_id;
                    $gift_user_record->bind_phone = $phone;
                    $gift_user_record->user_type = $new_flag;

                    $cash_id = YII_DEBUG ? 86 : 167;

                    $record = array(
                    0 => array(
                        'cash_id' => $cash_id,
                        'status' => 1,
                    ),
                    );
                    $gift_user_record->records = json_encode($record);
                    $gift_user_record->created = TimeUtil::getNowDate();

                    if($gift_user_record->save()){
                        $service = new ActService($act_id);
                        $msg = '玩翻天通用扫码活动';
                        $status = $service->postGiftUserRecord($ps_user_id, $ps_user_token, $cash_id, $msg);

                        if($status){
                            $result['status'] = 1;
                            $gift_user_record->status = 1;
                            $gift_user_record->save();
                        }else{
                            $result['msg'] = '网络开小差了，请稍后再试';
                        }
                    }
                }
            }else{
                $result['msg'] = '该手机号已领取，请更换手机号';
            }
        }else{
            $result['msg'] = '手机号异常，请查证';
        }

        HttpUtil::out($result);
    }

    /**
     * 主页直接进入输入手机号的界面
     * author: MEX | mixmore@yeah.net
     */
    public function actionPoem()
    {
        $this->_setTitle();
        $this->tpl->assign('title', '楚天少儿诗歌朗诵大赛新老用户扫码领券活动');

        // 更新PV
        PageViewData::updatePv('楚天少儿诗歌朗诵大赛新老用户扫码领券活动-3.3');

        if (YII_DEBUG) {
            $user_vo = UserData::getUserById(1);
            UserLib::setNowUser($user_vo);
        }

        if (time() > $this->end_time) {
            $this->tpl->display('ad/poem/brought_out.html');
            Yii::app()->end();
        }

        $act_id = 2;

        if ($act_id == 2) {
            // 这里进行用户手机号是否存在的判断 如果存在就直接用该手机号领券
            $service = new ActService($act_id);
            $act_record_sun = $service->getCashSum($act_id);  // 当前抽奖的数据统计 1 老用户 0 新用户

            // 获取当前用户数据
            $user_vo = UserLib::getNowUser();
            $phone = $user_vo['phone'];
            $user_id = $user_vo['uid'];

            // 判断用户是否有手机号 这是对新老用户的判断依据
            if ($user_vo['phone']) {
                // 用户手机号存在 老用户
                // 判断目前抽奖是否超过额定数量
                if ($act_record_sun['old_sum'] < $this->old_sum) {
                    // 判断当前用户是否参与过抽奖
                    $act_record = ActUserRecordVo::model()->find('bind_phone = :bind_phone', array(':bind_phone' => $phone));
                    if (!$act_record) {
                        // 没有抽过奖就新建抽奖记录
                        $service->postActUserRecord(1, $user_id, $phone);
                        $result = $service->getCash($user_id, $phone, $user_vo['token']);
                        $this->tpl->assign('res', $result);
                    } else {
                        // 如果参与过抽奖就直接跳转成功页面
                        // 这里判断该用户代金券是否有漏发
                        if ($act_record->status == 0) {
                            $result = $service->getCash($user_id, $phone, $user_vo['token']);
                            $this->tpl->assign('res', $result);
                        }
                    }
                    $this->tpl->display('ad/poem/success.html');
                } else {
                    // 这里做超量判断
                    $this->tpl->display('ad/poem/brought_out.html');
                }
            } else {
                if ($act_record_sun['new_sum'] < $this->new_sum) {
                    // 用户手机号不存在 新用户
                    $this->tpl->display('ad/poem/index.html');
                } else {
                    // 这里做超量判断
                    $this->tpl->display('ad/poem/brought_out.html');
                }
            }
        } else {
            // 活动id不存在的时候
            $url = Yii::app()->createUrl('ad/act/poem/act_id/2');   /*/act/poem/act_id/2*/
            Yii::app()->request->redirect($url);
        }
    }

    /**
     * 获取代金券
     * author: MEX | mixmore@yeah.net
     */
    public function actionGetCash()
    {
        $act_id = intval($_GET['act_id']);
        $service = new ActService($act_id);
        $sms_service = new SmsService();
        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );

        $phone = $_POST['phone'];
        $code = intval($_POST['code']);

        // 判断验证码
        if ($sms_service->checkCode($phone, $code)) {
            $user_vo_wechat = UserLib::setNowUserByPhone($phone);
            if ($user_vo_wechat) {
                if ($act_record = ActUserRecordVo::model()->find('bind_phone = :bind_phone', array(':bind_phone' => $phone))) {
                    $result['msg'] = '手机号重复绑定';
                } else {
                    if ($service->postActUserRecord(0, $user_vo_wechat['uid'], $phone)) {
                        // 领奖
                        $result = $service->getCash($user_vo_wechat['uid'], $phone, $user_vo_wechat['token']);
                    } else {
                        $result['msg'] = '手机号绑定失败';
                    }
                }

            } else {
                $result['msg'] = '手机号绑定哎';
            }
        } else {
            $result['status'] = 0;
            $result['msg'] = '验证码错误, 或者验证码超时, 重新操作';
        }

        HttpUtil::out($result);
    }

    /**
     * 设置标题
     * author: MEX | mixmore@yeah.net
     */
    private function _setTitle()
    {
        $platform = HttpUtil::getPlatform();
        if ($platform['wft']) {
            $this->tpl->assign('title', '楚天少儿诗歌朗诵大赛新老用户扫码领券活动');
        }
    }


}