<?php

/**
 * 抽奖活动服务类
 *
 * @classname: LotteryService
 * @author   11942518@qq.com | quenteen
 * @date     2016-6-29
 */
class LotteryService extends Manager{
    public $lottery_id = null;
    public $lottery_vo = null;

    public $LOTTERY_CONFIG= array(
    1 => array(
            'start_time' => '2016-07-09 01:00',
            'end_time' => '2016-07-15 12:00',
            'policy' => 'random',//按概率随机抽取
            'type' => 'chance',
            'page' => array(
                'index' => 'ad/turntable/act_child.html',
                'record' => 'ad/turntable/lottery_record.html',),
            'reward_type_arr' => array(
                '1' => 1,
                '2' => 3,
                '4' => 1,),
    ),
    2 => array(
            'start_time' => '2016-09-10 01:00',
            'end_time' => '2016-10-15 12:00',
            'policy' => 'first_or_random',//第一次必中，随后按概率随机抽取
            'type' => 'chance',
            'page' => array(
                'index' => 'ad/god/god.html',
                'record' => 'ad/god/my_prise.html',),

            'reward_type_arr' => array(
                '1' => 10,
                '2' => 5,),
    ),
    3 => array(
            'start_time' => '2016-09-10 01:00',
            'end_time' => '2016-12-20 12:00',
            'policy' => 'random',//第一次必中，随后按概率随机抽取
            'spend_score' => 100,//单次抽奖耗费积分
            'type' => 'score',//抽奖类型：积分抽 | 次数抽
            'page' => array(
                'index' => 'ad/grasp/start_game_bak.html',
                'game' => 'ad/grasp/get_child.html',
                'luck' => 'ad/grasp/get_prise.html',
                'record' => 'ad/grasp/my_prise.html',),

            'reward_type_arr' => array(
                '1' => 1,
                '2' => 1,),
    ),

    4 => array(
            'start_time' => '2016-10-26 01:00',
            'end_time' => '2016-11-20 12:00',
            'policy' => 'first_or_random',//第一次必中，随后按概率随机抽取
            'type' => 'chance',
            'show_period' => array(
                'one' => array('2016-11-01', '2016-11-02', '2016-11-03',),
                'two' => array('2016-11-04', '2016-11-05', '2016-11-06', '2016-11-07',),
                'three' => array('2016-11-08', '2016-11-09',),
                'four' => array('2016-11-10', '2016-11-11',),
    ),
            'page' => array(
                'index' => 'ad/sale/sale_index.html',
                'luck' => 'ad/sale/sale_game.html',
                'record' => 'ad/sale/sale_prise.html',),

            'reward_type_arr' => array(
                '1' => 5,
                '2' => 3,
                '3' => 3,),
    ),

    6 => array(
            'start_time' => '2017-01-22 00:00',
            'end_time' => '2017-02-05 10:00',
            'policy' => 'first_or_random',                             //第一次必中，随后按概率随机抽取
            'spend_score' => 50,                                        //单次抽奖耗费积分
            'type' => 'score',                                         //抽奖类型：积分抽 | 次数抽
            'page' => array(
                'index' => 'ad/battle/battle_index.html',
                'game' => 'ad/battle/battle_game.html',
                'luck' => 'ad/battle/battle_prise.html',
                'record' => 'ad/battle/battle_my.html',
                'prise' => 'ad/battle/battle_result.html',
    ),
            //中奖次数
             'reward_type_arr' => array(
                '1' => 1,
                '2' => 1,
                '3' => 1,
                '4' => 1,
                '5' => 1,
                 ),
    ),



    );

    public function __construct($lottery_id)
    {
        $this->lottery_id = intval($lottery_id);
        $this->lottery_vo = LotteryVo::model()->find('id = :id', array(
            ':id' => $lottery_id,
        ));
    }

    public function getConfig(){
        $lottery_config = $this->LOTTERY_CONFIG[$this->lottery_id];

        return $lottery_config;
    }

    //根据中奖策略，优先判断是否中奖
    public function checkLuckyByPolicy($user_id, $policy){
        $lucky_flag = false;
        switch ($policy){
            case 'random':
                break;
            case 'first_or_random':
                $sql = "select count(*) from ps_lottery_user_record
                        where lottery_id = {$this->lottery_id}
                        and user_id = {$user_id}";
                $total = Yii::app()->db->createCommand($sql)->queryScalar();
                if($total == 0){
                    $lucky_flag = true;
                }
                break;
            default:
                $lucky_flag = false;
                break;
        }

        return $lucky_flag;

    }

    /**
     * 检测用户是否中奖，并更新用户中奖纪录和抽奖次数
     * 1、外层整体中奖概率 - 2、内层根据各奖品数量计算中某个奖品的概率 - 3、最后计算用户获取该类型奖品的次数是否达到上限
     * type : chance(机会) | score(积分)
     * @return
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-30 上午10:23:59
     */
    public function lucky($user_id, $type = 'chance')
    {
        $special_type = 100;
        $lucky_flag = false;
        $is_win = 0;
        $chance = floatval($this->lottery_vo['chance']);

        $lottery_policy = $this->LOTTERY_CONFIG[$this->lottery_id]['policy'];
        $lucky_flag = $this->checkLuckyByPolicy($user_id, $lottery_policy);    //6号活动返回false

        //优先策略失败，则进入正常的概率判断
        if($lucky_flag == false){
            $max = 100;
            $target_num = intval($chance * $max);
            $target_num = $target_num > $max ? $max : $target_num;

            $target_random = rand(1, $max);

            if($target_random < $target_num){
                $lucky_flag = true;
            }
        }

        //满足外层中奖概率 - 内层根据各奖品数量计算中某个奖品的概率 - 最后计算用户获取该类型奖品的次数是否达到上限
        if ($lucky_flag) {
            $cash_vo = $this->getLuckLotteryCashVo();

            //在有奖品的前提下，继续抽奖
            if ($cash_vo) {
                //抽中某奖品，仍需计算他是否已经拿过该奖品
                $is_win = 1;

                $cash_id = $cash_vo['cash_id'];
                $cash_type = $cash_vo['type'];

                //判定用户是否中过该奖品及其次数

                if ($cash_type == $special_type) {//统计特等奖的中奖次数，不跟具体的现金券挂钩
                    $sql = "select count(*) as total from ps_lottery_user_record as record
                            inner join ps_lottery_cash as cash
                            on (record.lottery_id = cash.lottery_id
                            and record.lottery_cash_id = cash.id)
                            where record.lottery_id = {$this->lottery_id}
                            and record.user_id = {$user_id}
                            and cash.type = {$special_type}
                            and record.is_win = 2";
                } else {//单个普通奖的中奖次数 - 具体到cash_id
                    $sql = "select count(*) as total from ps_lottery_user_record
                            where lottery_id = {$this->lottery_id}
                            and user_id = {$user_id}
                            and is_win = 2
                            and cash_id = {$cash_id}";
                }

                $reward_total = Yii::app()->db->createCommand($sql)->queryScalar();

                $type_arr = $this->LOTTERY_CONFIG[$this->lottery_id]['reward_type_arr'];

                $reward_max = $type_arr[$cash_type];
                //仍可以获取该奖，则返回奖品详情
                if ($reward_total < $reward_max) {
                    $is_win = 2;
                }
            }
        }

        $save_result = $this->saveLotteryUserRecord($user_id, $is_win, $cash_vo, $type);
        //中奖 & 保存中奖记录成功
        if ($is_win == 2 && $save_result['flag']) {
            $cash_vo['record_id'] = $save_result['record_id'];
            return $cash_vo;
        } else {
            return false;
        }
    }

    /**
     * 获取抽中的奖品对象
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-30 上午09:31:32
     */
    public function getLuckLotteryCashVo()
    {

        $special_type = 100;
        $log_date = TimeUtil::getNowDate();

        $city = StringUtil::getCustomCity();
        $city = StringUtil::getCityForCn($city);

        //获取普通奖品 和 特殊奖品 及其剩余数量
        //type : 100 为特等奖，log_date有值
        //type : !100 为普通奖，log_date无值
        $sql = "select *, (total - send_total) as left_total from ps_lottery_cash
                where lottery_id = {$this->lottery_id}
                and city = '{$city}'
                and (total - send_total > 0)
                and (
                    (type != {$special_type})
                    or
                    (log_date = '{$log_date}' and type = {$special_type})
                )";

        $cmd = Yii::app()->db->createCommand($sql);
        $source = $cmd->queryAll();

        if ($source) {
            //将所有奖品按数量大小散列在一个数组里面，随机取出一个奖品
            $result = array();
            foreach ($source as $key => $val) {
                $left_total = $val['left_total'];

                for ($i = 0; $i < $left_total; $i++) {
                    $result[] = $val;
                }
            }

            //乱序
            shuffle($result);

            //随机获取
            $target_index = array_rand($result, 1);
            $cash_vo = $result[$target_index];

            unset($cash_vo['total']);
            unset($cash_vo['send_total']);
            unset($cash_vo['left_total']);
            unset($cash_vo['created']);
            unset($cash_vo['updated']);

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
    public function saveLotteryUserRecord($user_id, $is_win, $cash_vo = array(), $type)
    {
        $log_date = TimeUtil::getNowDate();

        $user_record_vo = new LotteryUserRecordVo();
        $user_record_vo->log_date = $log_date;
        $user_record_vo->lottery_id = $this->lottery_id;
        $user_record_vo->user_id = $user_id;
        $user_record_vo->is_win = $is_win;
        if ($is_win > 0) {
            $user_record_vo->lottery_cash_id = $cash_vo['id'];
            $user_record_vo->cash_id = $cash_vo['cash_id'];
            $user_record_vo->money = $cash_vo['money_price'];
        }
        $user_record_vo->status = 0;

        $created_time = TimeUtil::getNowDateTime();
        $user_record_vo->created = $created_time;
        $user_record_vo->updated = TimeUtil::getNowDateTime();

        if ($user_record_vo->save()) {
//            $record_vo = LotteryUserRecordVo::model()->find("log_date = '{$log_date}'
//                                                         and lottery_id = {$this->lottery_id}
//                                                         and user_id = {$user_id}
//                                                         and is_win = {$is_win}
//                                                         and created = '{$created_time}'");
            $record_id = $user_record_vo->id;
            $result['record_id'] = $record_id;

            if($type == 'chance'){
                //更新用户已抽奖次数
                $sql = "update ps_lottery_user_total
                    set op_total = op_total + 1
                    where log_date = '{$log_date}'
                    and lottery_id = {$this->lottery_id}
                    and user_id = {$user_id}";

                $update = Yii::app()->db->createCommand($sql)->execute();

            }else if($type == 'score'){
                //更新积分信息
                $config = $this->getConfig();

                $score_vo             = new LotteryScoreRecordVo();
                $score_vo->log_date   = TimeUtil::getNowDate();
                $score_vo->lottery_id = $this->lottery_id;
                $score_vo->user_id    = $user_id;
                $score_vo->type       = 'game';
                $score_vo->score      = 0 - $config['spend_score'];
                $score_vo->created    = TimeUtil::getNowDateTime();
                $score_vo->updated    = TimeUtil::getNowDateTime();

                $score_vo->save();
            }

            if ($is_win == 2) {
                //更新现金券发放数量
                $lottery_cash_id = $cash_vo['id'];
                $cash_sql = "update ps_lottery_cash
                            set send_total = send_total + 1
                            where lottery_id = {$this->lottery_id}
                            and id = {$lottery_cash_id}";

                $update = Yii::app()->db->createCommand($cash_sql)->execute();

                $result['flag'] = true;
            }
        } else {
            $result['flag'] = false;
        }

        return $result;
    }

    //计算用户的剩余抽奖次数
    public function getLotteryUserLeftTotal($user_id){
        //抽奖总次数
        $total = intval($this->lottery_vo['total']);

        $left_total = 0;//剩余次数
        $op_total = 0;//已抽次数

        $user_id = intval($user_id);

        if($user_id > 0){
            $now = TimeUtil::getNowDate();
            $user_total_vo = LotteryUserTotalVo::model()->find("log_date = '{$now}' and lottery_id = {$this->lottery_id} and user_id = {$user_id}");
            if ($user_total_vo) {
                $total = intval($user_total_vo['total']);
                $op_total = intval($user_total_vo['op_total']);
            } else {

                $user_total_vo = new LotteryUserTotalVo();
                $user_total_vo->log_date = $now;
                $user_total_vo->lottery_id = $this->lottery_id;
                $user_total_vo->user_id = $user_id;
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

    /**
     * 获取抽奖记录：默认获取全部抽奖记录
     * @param is_win - 0/1:未中奖 | 2:中奖 | 3:全部
     * @return
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-29 下午06:35:44
     */
    public function getLotteryUserRecord($user_id, $is_win = 3)
    {
        $user_id = intval($user_id);

        $sql = "select record.*, cash.cash_name, cash.cash_alias, cash.cash_image, cash.cash_end_time, cash.type from ps_lottery_user_record as record
                inner join ps_lottery_cash as cash
                on (record.lottery_id = cash.lottery_id
                and record.lottery_cash_id = cash.id)
                where record.lottery_id = {$this->lottery_id}
                and record.user_id = {$user_id}";

        if ($is_win != 3) {
            $sql .= " and is_win = {$is_win} order by status asc, created desc ";
        } else {
            $sql .= " order by status asc, created desc ";
        }

        $source = Yii::app()->db->createCommand($sql)->queryAll();

        return $source;
    }

    //获取未领取奖品的数量
    public function getLotteryUserRecordUndoingTotal($user_id, $is_win = 3){
        $user_id = intval($user_id);

        $sql = "select count(*)
                from ps_lottery_user_record as record
                where record.lottery_id = {$this->lottery_id}
                and record.user_id = {$user_id}
                and status = 0";

        if ($is_win != 3) {
            $sql .= " and is_win = {$is_win} order by status asc, created desc ";
        } else {
            $sql .= " order by status asc, created desc ";
        }

        $result = Yii::app()->db->createCommand($sql)->queryScalar();

        return $result;
    }

    //获取抽奖活动的所有商品
    public function getAllLotteryProduct($type = 'common')
    {
        if ($type == 'common') {
            $source = self::_getAllLotteryProductByProductType(0);
            $result = array();

            foreach ($source as $key => $val) {
                $type = $val['type'];

                $result[$type][] = $val;
            }

        } else {
            $result = self::_getAllLotteryProductByProductType(1);;
        }
        return $result;
    }

    /**
     * 根据id，从线上获取真实商品的属性，并进行填充
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-10-27 下午03:42:22
     */
    public function processLotteryProductForOnline($source, $show_period = 'one'){
        $result = array();

        $cookie_city = StringUtil::getCustomCity();

        foreach($source as $key => $val){
            $type = $key;
            foreach($val as $p_key => $p_val){
                $product_type = $p_val['product_type'];
                $product_id = $p_val['product_id'];
                $city = $p_val['city'];
                $period = $p_val['period'];

                if($city == $cookie_city && $show_period == $period){
                    $cache_key = 'taobao_sale_c' . $product_type . '_' . $product_id;
                    $cache_product = CacheUtil::get($cache_key);

                    if($cache_product){
                        $result[$type][] = $cache_product;
                    }else{
                        if($product_type == 0){//商品
                            $lib = new TicketLib();
                            $res = $lib->commodityDetail(array('id' => $product_id));

                            if($res['status'] == 1){
                                $data = $res['data'];

                                $p_val['product_name'] = $data['title'];
                                $p_val['product_image'] = $data['cover'];
                                $p_val['product_buy'] = $data['buy'];
                                $p_val['new_price'] = $data['low_price'];;

                                $now_time = TimeUtil::getNowDateTime();
                                $start_sale_time = TimeUtil::getDateTimeFromTime($data['start_sale_time']);
                                $end_sale_time = TimeUtil::getDateTimeFromTime($data['end_sale_time']);
                                $surplus_num = $data['surplus_num'];

                                if($now_time < $start_sale_time && $surplus_num > 0){
                                    $btn_status = 0;//即将开始
                                }else if($now_time >= $start_sale_time && $now_time <= $end_sale_time && $surplus_num > 0){
                                    $btn_status = 1;
                                }else{
                                    $btn_status = 2;
                                }
                                $p_val['btn_status'] = $btn_status;
                            }
                        }else{//活动
                            $lib = new PlayLib();
                            $res = $lib->playInfo(array('id' => $product_id));

                            if($res['status'] == 1){
                                $data = $res['data'];

                                $p_val['product_name'] = $data['title'];
                                $p_val['product_image'] = $data['cover'];
                                $p_val['product_buy'] = $data['recently_num'];
                                $p_val['new_price'] = $data['price'];

                                $btn_status = $data['btn_status'];
                                $btn_status = $btn_status == 1 ? 1 : 0;
                                $p_val['btn_status'] = $btn_status;
                            }
                        }

                        $result[$type][] = $p_val;
                        CacheUtil::set($cache_key, $p_val, 5 * 60);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 对商品数据进行可用性过滤
     *
     * @param $type_flag
     *
     * @return array
     * author: MEX | mixmore@yeah.net
     */
    private function _getAllLotteryProductByProductType($type_flag)
    {
        $now_time = time();

        $sql_1 = "select * from ps_lottery_product
                  where lottery_id = {$this->lottery_id}
                  and status = 1 ";
        if ($type_flag == 0) {
            $sql_1 .= "and type < 5 ";
        } elseif ($type_flag == 1) {
            $sql_1 .= "and type = 5 ";
        }
        $sql_1 .= "and product_type = 0
                   and product_id in (
                       select product_id from play_organizer_game
                       where start_time <= {$now_time}
                       and end_time >= {$now_time}
                       and status = 1
                       )";
        $source_1 = Yii::app()->db->createCommand($sql_1)->queryAll();

        $sql_2 = "select * from ps_lottery_product
                      where lottery_id = {$this->lottery_id}
                      and status = 1 ";
        if ($type_flag == 0) {
            $sql_2 .= "and type < 5 ";
        } elseif ($type_flag == 1) {
            $sql_2 .= "and type = 5 ";
        }
        $sql_2 .= "and product_type = 1
                      and product_id in (
                          select id from play_excercise_base
                          where release_status = 1
                          )";
        $source_2 = Yii::app()->db->createCommand($sql_2)->queryAll();
        $res = array_merge($source_1, $source_2);
        return self::array_sort($res, 'seq');
    }

    //获取今日特等奖
    public function getTodaySpecialCash()
    {
        $log_date = TimeUtil::getNowDate();

        $sql = "select * from ps_lottery_cash
                where log_date = '{$log_date}'
                and lottery_id = {$this->lottery_id}
                and type = 4";

        $result = Yii::app()->db->createCommand($sql)->queryRow();

        return $result;
    }

    //获取所有男神
    public function getAllSuperstar(){

        $sql = "select * from ps_lottery_superstar
        where lottery_id = {$this->lottery_id}
        and status = 1
        order by total desc";

        $result = Yii::app()->db->createCommand($sql)->queryAll();

        return $result;
    }

    //获取投票男神信息和投票人详细结果
    public function getVoteDetailByVoteId($vote_id, $lottery_id){
        $sql = "select plsv.*, pu.uid, pu.username, pu.img
        from ps_lottery_superstar_vote as plsv
        inner join play_user as pu
        on (plsv.user_id = pu.uid)
        where plsv.id = {$vote_id}
        and lottery_id = {$lottery_id}";

        $result = Yii::app()->db->createCommand($sql)->queryRow();

        return $result;
    }

    //给男神投票
    public function voteSuperstar($star_id){
        $star_vo = LotterySuperstarVo::model()->findByPk($star_id);
        $user_vo = UserLib::getNowUser();

        if($star_vo && $user_vo){
            $user_id = $user_vo['uid'];
            $time = TimeUtil::getNowDateTime();

            $vo = new LotterySuperstarVoteVo();
            $vo->lottery_id = $this->lottery_id;
            $vo->star_id = $star_id;
            $vo->user_id = $user_id;
            $vo->created = $time;
            $vo->updated = $time;

            if($vo->save()){
                $sql = "update ps_lottery_superstar
                        set total = total + 1
                        where lottery_id = {$this->lottery_id}
                        and id = {$star_id}";

                $result = Yii::app()->db->createCommand($sql)->execute();

                return $vo->id;
            }
        }

        return $result;
    }

    //获取用户投票的最新结果
    public function getVoteByUserId($user_id){
        $vote_vo = LotterySuperstarVoteVo::model()->find('lottery_id = :lottery_id and user_id = :user_id order by id desc', array(
            ':lottery_id' => $this->lottery_id,
            ':user_id' => $user_id,
        ));

        if($vote_vo){
            return $vote_vo['id'];
        }else{
            return 0;
        }
    }

    public function getLotteryLoginRecord($user_id){
        $user_id = intval($user_id);
        $login_vo = LotteryLoginRecordVo::model()->find("lottery_id = {$this->lottery_id} and user_id = {$user_id} order by phone desc");
        return $login_vo;
    }

    public function processLotteryLoginRecord($user_id, $channel = '', $phone = ''){
        $user_id = intval($user_id);

        if($user_id > 0){
            $channel = $channel == '' ? 'wft_wechat' : $channel;

            $log_date = TimeUtil::getNowDate();

            $login_vo = LotteryLoginRecordVo::model()->find("lottery_id = {$this->lottery_id} and user_id = {$user_id} and log_date = '{$log_date}'");

            if(!$login_vo){
                $login_vo = new LotteryLoginRecordVo();
                $login_vo->lottery_id = $this->lottery_id;
                $login_vo->user_id = $user_id;
                $login_vo->log_date = $log_date;
                $login_vo->guess_total = 5;
                $login_vo->channel = $channel;
                $login_vo->phone = $phone;
                $login_vo->created = TimeUtil::getNowDateTime();
                $login_vo->updated = TimeUtil::getNowDateTime();

                $login_vo->save();
            }else{
                if($login_vo->phone == '' && $phone != ''){
                    $login_vo->phone = $phone;
                    $login_vo->save();
                }
            }
        }

        return $login_vo;
    }

    /**
     * 保存用户点击分享的记录，同时更新用户抽奖的总次数
     * @author 11942518@qq.com | quenteen
     * @date   2016-6-30 上午09:12:52
     */
    public function updateUserTotalForShare($share_user_id, $click_user_id)
    {
        $log_date = TimeUtil::getNowDate();
        $total_limit = intval($this->lottery_vo['total_limit']);
        $total_limit = $total_limit == 0 ? 20 : $total_limit;

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
                where log_date = '{$log_date}'
                and lottery_id = {$this->lottery_id}
                and user_id = {$share_user_id}
                and total < {$total_limit}";
        $update = Yii::app()->db->createCommand($sql)->execute();

        return true;
    }

    /**
     * 判断现有用户是否已经点击过链接
     * 在未点击的情况下进行加一
     * 已点击的情况下返回false
     *
     * @param $share_user_id
     * @param $click_user_id
     */
    public function updateUserShareClick($share_user_id, $click_user_id)
    {
        // 检查参数防注入
        $share_user_id = intval($share_user_id);
        $click_user_id = intval($click_user_id);

        if ($share_user_id == 0 || $click_user_id == 0 || $share_user_id == $click_user_id || $this->_checkClicked($share_user_id, $click_user_id)) {
            return false;
        } else {
            // 这里调用这个方法 在 total 表中产生了今天最新的记录
            // 应该有别的方式代替 会更好
            $this->getLotteryUserLeftTotal($share_user_id);
            if ($this->updateUserTotalForShare($share_user_id, $click_user_id)) {
                return true;
            }
            return false;
        }
    }


    /**
     * @param $share_user_id
     * @param $click_user_id
     */
    private function _checkClicked($share_user_id, $click_user_id)
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

    //发奖
    public function acceptLotteryRecord($record_id, $user_vo)
    {
        $result = HttpUtil::getHttpResult();

        $user_id = $user_vo['uid'];
        $phone = $user_vo['phone'];

        //根据user_id、reward_id获取是否真有该获奖记录
        $sql = 'id = :record_id
                and lottery_id = :lottery_id
                and user_id = :user_id
                and is_win = 2
                and status = 0';

        $record = LotteryUserRecordVo::model()->find($sql, array(
            ':record_id' => $record_id,
            ':lottery_id' => $this->lottery_id,
            ':user_id' => $user_id,
        ));

        //存在，则可以领奖
        if($record){
            $record->bind_phone = $phone;
            $record->updated = TimeUtil::getNowDateTime();
            $record->status = 1;
            $lottery_cash_id = $record['lottery_cash_id'];
            $lottery_cash_vo = LotteryCashVo::model()->findByPk($lottery_cash_id);

            $cash_type = $lottery_cash_vo['type'];

            //发送奖品核心逻辑重构
            $accountlib = new AccountLib();
            $couponlib = new CouponLib();
            switch($cash_type){
                case 1://商品
                case 2://活动
//                    $act_service = new ActService(0);
//                    $send_gift_status = $act_service->postGiftUserRecord($user_id, $user_vo['token'], $record->cash_id, $this->lottery_vo['name']);
//                    $send_gift_result = array(
//                        'status' => $send_gift_status,
//                    );
                    $data = array(
                        'uid' => $user_id,
                        'id' => $record->cash_id,
                        'msg'=>"2017新年活动"
                    );

                    $send_gift_status = $couponlib->postGiftUserRecord($data);
                    $send_gift_result = array(
                        'status' => $send_gift_status['status'],
                        'data' => $send_gift_status['data'],
                    );
                    break;

                case 3://现金
                    $data = array(
                        'uid' =>$user_id,
                        'record_id' => $record->id,
                    );
                    $send_gift_status = $accountlib->lotteryWin($data);
                    $send_gift_result = array(
                        'status' => $send_gift_status['status'],
                        'data' => $send_gift_status['data'],
                    );
                    break;

                case 4://亲子游
                    $data = array(
                        'uid' =>$user_id,
                        'record_id' => $record->id,
                    );

                    $send_gift_status = $accountlib->activityFreeCouponForLottery($data);
                    $send_gift_result = array(
                        'status' => $send_gift_status['status'],
                        'data' => $send_gift_status['data'],
                    );
                    break;

                case 5:
                    $send_gift_result = array(
                        'status' => 1,
                        'data'=> array(
                            'status' => 2,
                        ),
                    );
                    $record->status = 0;                       //发放状态由后台做出改变发过短信则为1
                    break;

                case 6:
                    break;
                case 101:
                    break;
                default:
                    $always_right = true;
                    break;
            }

            if($send_gift_result['data']['status'] == 1){
                $result['status'] = 1;
                $result['msg'] = '领取成功';

                $save_flag = $record->save();//更新发放状态

            }elseif ($send_gift_result['data']['status'] == 2){
                $result['status'] = 1;
                $result['msg'] = '我们的工作人员将在5个工作日内以短信形式发送兑换码到您的玩翻天账户绑定的手机号上，注意查收！';

                $save_flag = $record->save();//更新发放状态
            }else{
                $record->status = 0;
                $save_flag = $record->save();

                $result['msg'] = '网络开小差了，领取现金券失败，请刷新重试';
            }

            if(YII_DEBUG){//debug模式，始终领取成功
//                $record->status = 1;
//                $save_flag = $record->save();
//                $result['status'] = 1;
            }
            //end

            if($always_right){
                $result['status'] = 1;
            }
        }else{
            $result['msg'] = '您已领取该奖品，请刷新后重新查看';
        }

        return $result;
    }

    //分享文案调用
    public function getAppShareConfig($controller, $config = array()){
        $lottery_id = $this->lottery_id;

        $service = new ShareConfigService($lottery_id);
        switch($lottery_id){
            case 1:
                $wechat_config = $service->getShareConfigForHolidayAd($config['user_id']);
                break;
            case 2:
                $wechat_config = $service->getShareConfigForBoyGodAd($config['user_id'], $config['vote_id']);
                break;
            case 3:
                $wechat_config = $service->getShareConfigForGeeguAd($config['user_id']);
                break;
            case 4:
                $wechat_config = $service->getShareConfigForTaobaoSale($config['user_id']);
                break;
            case 5:
                ;
                break;
            case 6:
                $wechat_config = $service->getShareConfigForBattle($config['user_id'],$config['user_name']);
                break;
            default:
                $wechat_config = array();
                break;
        }

        $controller->tpl->assign('wechat_share', json_encode($wechat_config));

        $platform = HttpUtil::getPlatform();
        if($platform['wechat']){
            $controller->tpl->assign('wechat_flag', true);
        }
    }

    //获取当前应该展现的期数
    public function getLotteryShowPeriod($show_period){
        $now = TimeUtil::getNowDate();

        $result = 'one';
        foreach($show_period as $key => $val){
            foreach($val as $t_key => $t_val){
                if($now == $t_val){
                    $result = $key;
                }
            }
        }

        return $result;
    }

    public function array_sort($arr, $keys, $type = 'asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /*
     * =====================================  双十一活动答题 ===================================
     */
    //
    // 获取题目
    public function getSubject()
    {
        // 可以使用入参arr来保存之前返回过的题目避免重复
        $city = StringUtil::getCustomCity();

        // 获取相应的参数
        $url = $this->getUrl($city);
        $subject_list = $this->getParam($city);
        $subject_list_source = $subject_list;


        shuffle($subject_list);
        $subject_list_choice = array(
            '1' => array(
                'title' => array_search($subject_list[0], $subject_list_source),
                'url' => $url.$subject_list[0]
        ),
            '2' => array(
                'title' => array_search($subject_list[1], $subject_list_source),
                'url' => $url.$subject_list[1]
        ),
            '3' => array(
                'title' => array_search($subject_list[2], $subject_list_source),
                'url' => $url.$subject_list[2]
        ),
        //            '4' => array(
        //                'title' => array_search($subject_list[3], $subject_list_source),
        //                'url' => $url.$subject_list[3]
        //            ),
        );


        //////////todo
        shuffle($subject_list_choice);

        // 获取
        // 获取正确选项
        $res = array(
            'right' => array(
                'title' => array_search($subject_list[0], $subject_list_source),
                'url' => $url.$subject_list[0]
        ),
            'choice' => $subject_list_choice
        );
        return $res;
    }

    private function getParam($city)
    {
        if ($city == 'nanjing') {
            $res = array(
                "牛首山花嬉谷" => "niushoushanhuaxigu.jpg",
                "杨柳湖" => "yangliuhu.jpg",
                "大报恩寺" => "dabaoensi.jpg",
                "水慢城" => "shuimancheng.jpg",
                "珍珠泉" => "zhenzhuquan.jpg",
                "牛首山" => "niushoushan.jpg",
                "湖熟菊花展" => "hushujuhuazhan.jpg",
                "沙漠风情园" => "shamofengqingyuan.jpg",
                "上海迪士尼" => "shanghaidishini.jpg",
                "上海科技馆" => "shanghaikejiguan.jpg",
                "银杏湖" => "yinxinghu.jpg",
                "扬州国际露营地" => "yangzhouguojiluyingdi.jpg",
                "紫清湖温泉" => "ziqinghuwenquan.jpg",
                "大吉温泉" => "dajiwenquan.jpg",
                "卓玛海洋探险乐园" => "zhuomahaiyangtanxianleyuan.jpg",
                "粘粘屋" => "niannianwu.jpg",
                "嘉乐迪" => "jialedi.jpg",
                "魔法城" => "mofacheng.jpg",
                "龙格" => "longge.jpg",
                "悠游子鱼" => "youyouziyu.jpg",
                "梦乐城" => "menglecheng.jpg",
                "金洋宝贝城" => "jinyangbaobeicheng.jpg",
                "雨花台游乐园" => "yuhuataiyouleyuan.jpg",
                "贝缤纷" => "beibinfen.jpg",
                "奇趣屋" => "qiquwu.jpg",
                "海底世界" => "haidishijie.jpg",
                "孩子王童乐园" => "haiziwangtongleyuan.jpg",
                "卡通尼乐园" => "katongnileyuan.jpg",
                "梵高艺术展" => "fangaoyishuzhan.jpg",
                "万驰" => "wanchi.jpg",
                "国防园" => "guofangyuan.jpg",
                "丹德齿科" => "dandechike.jpg",
                "国王击剑" => "guowangjijian.jpg",
                "悠游堂" => "yoyoutang.jpg",
                "南京建邺区图书馆" => "nanjingjianyequtushuguan.jpg",
                "丽山音乐农场" => "lishanyinyuenongchang.jpg",
                "交管局" => "jiaoguanju.jpg",
                "俊红" => "junhon.jpg",

            );
        } else {
            $res = array(
                "33°婴幼儿游泳拓展训练馆"          => "33yingyouer.png",
                "403国际艺术中心儿童区"            => "403guojiyishuzhongxin.png",
                "啊哈儿童成长乐园"                => "ahaertongchengzhangleyuan.png",
                "奥山冰雪主题公园"                => "aoshanbingxue.png",
                "百步亭巴博泡泡馆"                => "baibuting.png",
                "贝乐园"                     => "beileyuan.png",
                "冰雪奇园"                    => "bingxueqiyuan.png",
                "布噜么么儿童乐园"                => "bulumomo.png",
                "戴蒙儿童剪发造型沙龙"              => "daimengertongjianfazaoxing.png",
                "多奇妙儿童乐园"                 => "duoqimiaoertongleyuan.png",
                "哈你运动馆"                   => "haniyundongguan.png",
                "孩子王"                     => "haiziwang.png",
                "汉街万达儿童乐园"                => "haijiewanda.png ",
                "花海乐园"                    => "huahaileyuan.png",
                "环球世宇乐园"                  => "huanqiushiyu.png",
                "幻贝家"                     => "huanbeijia.png",
                "黄石兰博基尼酒店"                => "huangshilanbojini.png",
                "荟聚宝贝"                    => "huijibaobei.png",
                "金贝儿儿童乐园"                 => "jinbeierertongleyuan.png",
                "卡通尼乐园"                   => "katongnileyuan.png",
                "罗田隐居三里度假农庄"              => "luotian.png",
                "麦鲁小城"                    => "mailuxiaocheng.png",
                "木奇灵探险乐园"                 => "miqiling.png",
                "MollyFantasy莫莉幻想（永旺经开店）" => "MollyFantasy.png",
                "泡泡糖儿童恒温水上乐园"             => "paopaotang.png",
                "亲贝湾"                     => "qinbeiwan.png",
                "赛尔之城儿童乐园"                => "saierzhicheng.png",
                "泰迪厨房"                    => "taidichufang.png",
                "V-THREE-虚拟现实科技馆"         => "V-THREE.png",
                "维佳欢乐小镇"                  => "weijia.png",
                "武汉海昌极地海洋公园"              => "wuhanhaichang.png",
                "武汉恒大酒店"                  => "wuhanhengda.png",
                "武商众圆松松小镇"                => "wushangzhongyuansongsongxiaozhen.png",
                "西游记主题公园"                 => "xiyouji.png",
                "咸宁碧桂园凤凰温泉酒店"             => "xianningbiguiyuan.png",
                "小蝌蚪儿童室内水上乐园"             => "xiaokedou.png",
                "星期8小镇"                   => "xingqi8.png",
                "宜家冰雪王国"                  => "yijiabingxue.png",
                "悠游堂"                     => "youyoutang.png",
                "张公山寨"                    => "zhanggongshanzhai.png",
            );
        }
        return $res;
    }

    private function getUrl($city)
    {
        $url = 'ad/sale/';
        if ($city == 'nanjing') {
            $url .= 'nj_store/';
        } else {
            $url .= 'wh_store/';
        }
        return $url;
    }
    /*
     * =====================================  双十一活动答题 ===================================
     */

    public static function getVipLotteryId(){
        return 5;
    }

    //中奖跑马灯
    public static function horseRaceLamp($lottery_id){

        $sql = "select count(*) from ps_lottery_user_record
                        where lottery_id = {$lottery_id}
                        and is_win = 2";
        $total = Yii::app()->db->createCommand($sql)->queryScalar();
        $horse_race_lamp = array();

        if($total < 100){
            $user = LotteryData::getVirtualUser();
            $gift = LotteryData::getVirtualGift();

            for($i = 125; $i>115;$i--){
                $rand = rand(0,$i);

                $luck_name = $user[$rand]['username'];
                $gift_num = $user[$rand]['mod_num'];
                $gift_temp = $gift[$gift_num];

                $user[$rand] = $user[$i];           //交换避免进行取重校验
                $horse_race_lamp[]= array(
                    'user_name'=> $luck_name,
                    'gift_name'=> $gift_temp,
                );
            }

            return $horse_race_lamp;
        }else{

            $sql = "SELECT
                        pu.username,
                        plc.cash_name
                    FROM
                        ps_lottery_user_record AS plur
                    INNER JOIN ps_lottery_cash AS plc ON  plc.id = plur.lottery_cash_id
                    INNER JOIN play_user AS pu ON pu.uid = plur.user_id

                    WHERE
                        plur.lottery_id = 6
                    AND plur.is_win = 2
                    AND plur.`status` = 1";

            $cmd = Yii::app()->db->createCommand($sql);
            $source = $cmd->queryAll();

                //乱序
                shuffle($source);

                //随机获取
                $target_index = array_rand($source, 20);


            for($i=0;$i<10;$i++){
                $username = $source[$target_index[$i]]['username'];
                if(StringUtil::checkPhone($username)){
                    $username=StringUtil::processPhone($username);
                }

                $horse_race_lamp[]=array(
                    'user_name'=>$username,
                    'gift_name'=> $source[$target_index[$i]]['cash_name'],
                );
            }
        }

        return $horse_race_lamp;
    }

    //获取最好成绩
    public function getMaxScoreByUid($uid){
        $uid = intval($uid);
        $sql = "select MAX(score) from ps_lottery_score_record where lottery_id = {$this->lottery_id} and user_id = {$uid} and score >= 0";
        $max_score =  Yii::app()->db->createCommand($sql)->queryScalar();
        if($max_score == null){
            $max_score = 0;
        }
        return $max_score;
    }
}