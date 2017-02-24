<?php
/**
 *
 * @classname: WinService
 * @author 11942518@qq.com | quenteen
 * @date 2016-11-19
 */
class WinService extends Manager{
    public $lottery_id = null;
    public $lottery_vo = null;

    public function __construct($lottery_id){
        $this->lottery_id = intval($lottery_id);
        $this->lottery_vo = LotteryVo::model()->find('id = :id', array(
            ':id' => $lottery_id,
        ));
    }

    public function lucky($user_id)
    {
        $lucky_flag = false;
        $is_win = 0;
        $chance = floatval($this->lottery_vo['chance']);

        $max = 100;
        $target_num = intval($chance * $max);
        $target_num = $target_num > $max ? $max : $target_num;

        $target_random = rand(1, $max);

        if($target_random < $target_num){
            $lucky_flag = true;
        }

        //满足外层中奖概率 - 内层根据各奖品数量计算中某个奖品的概率 - 最后计算用户获取该类型奖品的次数是否达到上限
        $random_money = 0;
        if ($lucky_flag) {
            $random = rand(1, 6);
            $random_money = floatval($random / 10);

            //金额在一定范围内，则代表中奖
            if ($random_money) {
                //抽中奖品
                $is_win = 2;
            }
        }

        $save_result = $this->saveWinUserRecord($user_id, $is_win, $random_money, $type);
        //中奖 & 保存中奖记录成功
        if ($is_win == 2 && $save_result['flag']) {
            $cash_vo = array(
                'money' => $random_money,
            );
            return $cash_vo;
        } else {
            return false;
        }
    }

    /**
     * 更新用户抽奖记录，同时更新抽奖次数
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-30 上午08:11:03
     */
    public function saveWinUserRecord($user_id, $is_win, $money)
    {
        $log_date = TimeUtil::getNowDate();

        $user_record_vo = new LotteryUserRecordVo();
        $user_record_vo->log_date = $log_date;
        $user_record_vo->lottery_id = $this->lottery_id;
        $user_record_vo->user_id = $user_id;
        $user_record_vo->is_win = $is_win;
        if ($is_win == 2) {
            $user_record_vo->money = $money;
        }else{
            $user_record_vo->money = 0;
        }
        $user_record_vo->status = 0;
        $user_record_vo->created = TimeUtil::getNowDateTime();
        $user_record_vo->updated = TimeUtil::getNowDateTime();

        if ($user_record_vo->save()) {
            $record_id = $user_record_vo['id'];

            //更新用户已抽奖次数
            $sql = "update ps_lottery_user_total
                    set op_total = op_total + 1
                    where lottery_id = {$this->lottery_id}
                    and user_id = {$user_id}";

            $update = Yii::app()->db->createCommand($sql)->execute();

            if ($is_win == 2) {
                $param = array(
                    'record_id' => $record_id,
                );

                //发钱操作
                $account_rst = AccountLib::lotteryWin($param);

                $result['flag'] = true;
            }
        } else {
            $result['flag'] = false;
        }

        return $result;
    }

    //计算用户的剩余抽奖次数
    public function getWinUserLeftTotal($user_id){
        //抽奖总次数
        $may_total = 0;
        $total = 0;
        $op_total = 0;//已抽次数

        $user_id = intval($user_id);

        if($user_id > 0){
            $now = TimeUtil::getNowDate();
            $user_total_vo = LotteryUserTotalVo::model()->find("lottery_id = {$this->lottery_id} and user_id = {$user_id} order by id desc");
            if ($user_total_vo) {
                $total = intval($user_total_vo['total']);
                $op_total = intval($user_total_vo['op_total']);
            } else {
                $user_total_vo = new LotteryUserTotalVo();
                $user_total_vo->log_date = '2020-03-14';
                $user_total_vo->lottery_id = $this->lottery_id;
                $user_total_vo->user_id = $user_id;
                $user_total_vo->may_total = $may_total;
                $user_total_vo->total = $total;
                $user_total_vo->op_total = $op_total;
                $user_total_vo->created = TimeUtil::getNowDateTime();
                $user_total_vo->updated = TimeUtil::getNowDateTime();

                $user_total_vo->save();
            }
        }

        $left_total = $total - $op_total;
        $left_total = $left_total > 0 ? $left_total : 0;

        return $left_total;
    }

    //获取分享点击数
    public function getUserShareClick($user_id){
        $sql = "select count(*) as total from ps_lottery_share_click_record
        where lottery_id = {$this->lottery_id}
        and share_user_id = {$user_id}";

        $total = Yii::app()->db->createCommand($sql)->queryScalar();

        return $total;
    }

    public function updateUserShareClick($share_user_id, $click_user_id)
    {
        // 检查参数防注入
        $share_user_id = intval($share_user_id);
        $click_user_id = intval($click_user_id);

        if ($share_user_id == 0 || $click_user_id == 0 || $share_user_id == $click_user_id || $this->checkClicked($share_user_id, $click_user_id)) {
            return false;
        } else {
            // 这里调用这个方法 在 total 表中产生了今天最新的记录
            $this->getWinUserLeftTotal($share_user_id);
            if ($this->updateUserTotalForShare($share_user_id, $click_user_id)) {
                return true;
            }
            return false;
        }
    }

    public function updateUserTotalForShare($share_user_id, $click_user_id)
    {
        $log_date = TimeUtil::getNowDate();

        //1.保存分享点击记录
        $share_click_vo = new LotteryShareClickRecordVo();
        $share_click_vo->log_date = $log_date;
        $share_click_vo->lottery_id = $this->lottery_id;
        $share_click_vo->share_user_id = $share_user_id;
        $share_click_vo->click_user_id = $click_user_id;
        $share_click_vo->created = TimeUtil::getNowDateTime();
        $share_click_vo->updated = TimeUtil::getNowDateTime();

        $share_click_vo->save();

        //2.更新用户抽奖次数
        $sql = "update ps_lottery_user_total
                set total = total + 1
                where lottery_id = {$this->lottery_id}
                and user_id = {$share_user_id}
                and total < may_total";
        $update = Yii::app()->db->createCommand($sql)->execute();

        return true;
    }

    public function checkClicked($share_user_id, $click_user_id)
    {
        if($share_user_id > 0 && $click_user_id > 0){
            $sql = "select * from ps_lottery_share_click_record
                    where share_user_id = {$share_user_id}
                    and click_user_id = {$click_user_id}
                    and lottery_id = {$this->lottery_id}";

            $result = Yii::app()->db->createCommand($sql)->queryRow();
            if ($result) {
                return true;
            }
        }

        return false;
    }
}
