<?php

/**
 * 抽奖活动数据分析
 *
 * Class StatsController
 * author: MEX | mixmore@yeah.net
 */
class StatsController extends Controller{

    /**
     * 获取活动列表 展示活动名称 中奖概率
     * author: MEX | mixmore@yeah.net
     */
    public function actionGetLotteryList()
    {
        $res = LotteryVo::model()->findAll();
        $this->tpl->assign('lottery', $res);
        $this->tpl->display('ad/status/stats_lottery_list.html');
    }

    public function actionAddLotteryAttr(){
        $data = $_POST;

        $flag = true;

        $lottery_id = $data['lottery_id'];
        $type = $data['type'];

        switch($type){
            case 'cash':
                $vo = new LotteryCashVo();
                break;
            case 'product':
                $vo = new LotteryProductVo();
                break;
            default:
                $flag = false;
                break;
        }

        $result = HttpUtil::getHttpResult();
        if($flag){
            $vo->lottery_id = $lottery_id;
            $vo->save();
            $result['status'] = 1;
        }
        HttpUtil::out($result);
    }

    /**
     * 更新 展示活动名称 中奖概率 等相关数据
     * author: MEX | mixmore@yeah.net
     */
    public function actionUpdateLottery(){
        $lottery_id = intval($_GET['lottery_id']);
        $lottery = LotteryVo::model()->find('id=:id', array(':id' => $lottery_id));
        $lottery_cash_list = LotteryCashVo::model()->findAll('lottery_id = :lottery_id', array(
            ':lottery_id' => $lottery_id,
        ));

        if($_POST) {
            $data = $_POST;

            if ($data['chance']) {
                // 如果有数据提交就进行相应的数据更新
                if ($lottery) {
                    $lottery->name = $data['name'];
                    $lottery->detail = $data['detail'];
                    $lottery->total = $data['total'];
                    $lottery->total_limit = $data['total_limit'];
                    $lottery->chance = $data['chance'];
                    $lottery->save();
                }
            } else{
                $update_arr = array(
                    'type',
                    'city',
                    'cash_id',
                    'cash_name',
                    'cash_alias',
                    'total',
                );

                foreach ($lottery_cash_list as $key => $val) {
                    $flag = false;

                    foreach($update_arr as $update_name){
                        $post_key = $update_name . '_' . $val['id'];

                        if(isset($data[$post_key])){
                            $flag = true;
                            $val->$update_name = $data[$post_key];
                        }
                    }

                    if($flag){
                        $val->save();
                    }
                }
            }

            $this->redirect($this->createUrl('stats/updateLottery/lottery_id/' . $lottery_id));
            Yii::app()->end();
        }

        $res = array(
            'lottery_main_para' => $lottery,
            'lottery_cash' => $lottery_cash_list,
        );

        $this->tpl->assign('lottery_vo', $lottery);
        $this->tpl->assign('lottery', $res);
        $this->tpl->assign('lottery_id', $lottery_id);
        $this->tpl->assign('city', $city);
        $this->tpl->display('ad/status/lottery_para_modify.html');
    }

    /**
     * 获取活动列表 展示活动名称 中奖概率
     * author: MEX | mixmore@yeah.net
     */
    public function actionUpdateLotteryProduct(){
        $lottery_id = intval($_GET['lottery_id']);
        $city = trim($_REQUEST['city']);
        $lottery = LotteryVo::model()->find('id=:id', array(':id' => $lottery_id));
        $lottery_product_list = LotteryProductVo::model()->findAll('lottery_id = :lottery_id order by seq asc', array(
            ':lottery_id' => $lottery_id,
        ));

        if($_POST) {
            $data = $_POST;

            $update_arr = array(
                'type',
                'period',
                'during',
                'city',
                'product_id',
                'product_name',
                'product_image',
                'product_type',
                'new_price',
                'old_price',
                'sell_status',
                'seq',
                'status',
            );

            foreach ($lottery_product_list as $key => $val) {
                $flag = false;
                foreach($update_arr as $update_name){
                    $post_key = $update_name . '_' . $val['id'];

                    if(isset($data[$post_key])){
                        $flag = true;
                        $val->$update_name = $data[$post_key];
                    }
                }

                if($flag){
                    $val->save();
                }
            }

            $this->redirect($this->createUrl('stats/updateLotteryProduct/lottery_id/' . $lottery_id));
            Yii::app()->end();
        }

        $res = array(
            'lottery_product' => $lottery_product_list,
        );

        $this->tpl->assign('lottery', $res);
        $this->tpl->assign('lottery_id', $lottery_id);
        $this->tpl->assign('city', $city);
        $this->tpl->display('ad/status/lottery_product_modify.html');
    }


    /**
     * id、名称、总量、已发总量、特等奖的日期、特定讲数量和已发数量
     *
     * author: MEX | mixmore@yeah.net
     */
    public function actionGetLotteryStatsDetail()
    {
        $lottery_id = intval($_GET['lottery_id']);
        $cut_time = 1485014400;  //todo 写一个方法获取开始时间
        $service = new StatsService($lottery_id);

        $res = LotteryVo::model()->find("id = {$lottery_id}");
        $this->tpl->assign('lottery_vo', $res);

        $res = array(
            'lottery_cash'                => $lottery_cash = LotteryCashVo::model()->findAll('lottery_id=:lottery_id order by type asc', array(':lottery_id' => $lottery_id)),
            'lottery_user_record'         => array(
                'lottery_num'  => $service->getLotteryUserRecordStatistics($lottery_id, 0),
                'is_win_false' => $service->getLotteryUserRecordStatistics($lottery_id, 1),
                'is_win_true'  => $service->getLotteryUserRecordStatistics($lottery_id, 2),
                'awarded'      => $service->getLotteryUserRecordStatistics($lottery_id, 3),
        ),
            'lottery_gift_total' => $service->getLeftGiftTotal($lottery_id)['total'],
            'lottery_gift_left_total' => $service->getLeftGiftTotal($lottery_id)['left_total'],
            'lottery_shared_click_record' => $service->getLotteryShareClickRecordByLotteryId($lottery_id),
//            'about_user'=> array(
//                'new_user_total' => $service->getLotteryStatsUsersCountDetail(1,$cut_time,$lottery_id,0),
//                'new_user_total_for_day' => $service->getLotteryStatsUsersCountDetail(1,$cut_time,$lottery_id,1),
//                'old_user_total' => $service->getLotteryStatsUsersCountDetail(0,$cut_time,$lottery_id,0),
//            ),
        );

        $this->tpl->assign('lottery', $res);
        $this->tpl->assign('lottery_id', $lottery_id);
        $this->tpl->display('ad/status/stats_lottery_detail.html');
    }

    public function actionGetLotteryStatsUsers()
    {
        $lottery_id = intval($_GET['lottery_id']);
        $service = new StatsService($lottery_id);
        $res = $service->getLotteryStatsUsers();
        $cut_time = '1468166399';
        $user_count = array(
            'old_uv' => $service->getLotteryStatsUsersCount(0, $cut_time),
            'new_uv' => $service->getLotteryStatsUsersCount(1, $cut_time),
        );
        $this->tpl->assign('lottery_user_count', $user_count);
        $res = $this->_timestamp2date($res);
        $this->tpl->assign('lottery_user', $res);
        $this->tpl->assign('lottery_id', $lottery_id);
        $this->tpl->display('ad/status/lottery_stats_users.html');
    }

    /**
     *
     * 所有用户数（多个微信1个手机号、1个微信对应一个手机号、没有手机号……）
     * 多个微信对应一个手机号用户 数据
     * 1个微信对应一个手机号用户  数据
     * 1个微信号对应多个手机号的情况
     * 从app登陆用户、只从微信登陆用户
     *
     * author: MEX | mixmore@yeah.net
     */
    public function actionGetUsersData()
    {

        $service = new StatsUserService();
        $all_user = $service->getAllLoginUser();
        $wechat_user = $service->getWechatLoginUser();
        $app_user = $service->getAppLoginUser();

        $without_phone_user = $service->getWithoutPhoneUser();

        $one_user_many_wechat = $service->getOneUserManyWechat();
        $one_wechat_many_user = $service->getOneWechatManyUser();

        $one_wechat_many_phone = $service->getOneWechatManyPhone();
        $one_phone_many_wechat = $service->getOnePhoneManyWechat();
        $one_wechat_one_phone = $service->getOnePhoneOneWechat();

        $repeat_phone = $service->getRepeatPhone();

        $res = array(
            'app_login'    => $app_user['num'],
            'wechat_login' => $wechat_user['num'],
            'all_login' => $all_user['num'],

            'without_phone' => $without_phone_user['num'],

            'one_user_many_wechat' => count($one_user_many_wechat),
            'one_wechat_many_user' => count($one_wechat_many_user),

            'one_wechat_many_phone' => count($one_wechat_many_phone),
            'one_phone_many_wechat' => count($one_phone_many_wechat),
            'one_wechat_one_phone' => $one_wechat_one_phone['num'],

            'repeat_phone' => count($repeat_phone),
        );

        $this->tpl->assign('user_data', $res);
        $this->tpl->assign('lottery_id', $lottery_id);
        $this->tpl->display('ad/status/users_data.html');
    }

    public function actionGetLotteryUsersOld()
    {
        $cut_time = '1468166399';
        $lottery_id = intval($_GET['lottery_id']);
        $service = new StatsService($lottery_id);
        $old = $service->getLotteryStatsUsersCountDetail(0, $cut_time);
        $source = self::_timestamp2date($old);

        $this->export($source);
    }

    public function actionGetLotteryUsersNew()
    {
        $cut_time = '1468166399';
        $lottery_id = intval($_GET['lottery_id']);
        $service = new StatsService($lottery_id);
        $new = $service->getLotteryStatsUsersCountDetail(1, $cut_time);
        $source = self::_timestamp2date($new);

        $this->export($source);
    }

    private function _timestamp2date($source)
    {
        for ($i = 0; $i < count($source); $i++) {
            $source[$i]['dateline'] = date('Y-m-d H:i:s', $source[$i]['dateline']);
            $source[$i]['child_old'] = date('Y-m-d H:i:s', $source[$i]['child_old']);
        }
        return $source;
    }

    public function export($source)
    {
        header("Content-Type: application/vnd.ms-excel; charset=gb2312");
        header("Content-Disposition: attachment;filename=用户数据.csv ");

        $str = '';
        foreach ($source as $row) {
            $str_arr = array();
            foreach ($row as $column) {
                $str_arr[] = '"' . str_replace('"', '""', $column) . '"';
            }
            $str .= implode(',', $str_arr) . PHP_EOL;
        }

        echo $str;
        //        echo mb_convert_encoding($str,'utf-16','utf-8');
    }

    /**
     * 特殊类别中奖展示
     * @author rzfeng@wanfantian.com | rzfeng
     * @date   2017-01-19 14：23
     */
    public function actionLuckUserList(){
        $lottery_id = intval($_GET['lottery_id']);
        
        $record_vo = StatsService::getOtherTypeList($lottery_id);
        
        $this->tpl->assign('lottery_id', $lottery_id);
        $this->tpl->assign('record_vo',$record_vo);
        $this->tpl->display('ad/status/lottery_lucky_gift.html');
    }

    /**
     * 后台更改特殊
     * @author rzfeng@wanfantian.com | rzfeng
     * @date   2017-01-19 14：23
     */
    public function actionChangeStatus(){
        $lottery_record_id= intval($_GET['lottery_record_id']);

        StatsService::changeStatus($lottery_record_id);

    }
}
