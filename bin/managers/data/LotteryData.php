<?php

class LotteryData extends Manager{

    public static function getAllGodActForRank(){
        $result = array();

        $item = array(
            'title' => '涨知识|食油探秘+趣味科学实验',
            'name' => '涨知识|食油探秘+制作曲奇+趣味科学实验',
            'image' => '/static/img/ad/god/act/oil_secrets.png',
            'type' => 'null',
            'address' => '金龙鱼工厂',
            'total' => 182,
            'age' => '4岁',
            'rate' => 95,
            'act_id' => 0,   /*大于零0： 活动详情； 0：跳转到结果页*/
            'comment' => array(
                '有吃又有玩',
                '今天的日记可以写得很有意思',
                '科学实验好酷'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '去撒欢|海洋球嘉年华+舞蹈游戏',
            'name' => '去撒欢|海洋球嘉年华+舞蹈秀+游戏',
            'image' => '/static/img/ad/god/act/sea_ball.png',
            'type' => 'null',
            'address' => '南湖/光谷',
            'total' => 167,
            'age' => '2—8岁',
            'rate' => 90,
            'act_id' => 137,  /*大于零0： 活动详情； 0：跳转到结果页*/
            'comment' => array(
                '玩得很高兴，娃都不想走',
                '现场秩序维持得挺好',
                '还想再玩一次'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '走进科技馆，探索科学奥秘',
            'name' => '走进科技馆，探索科学奥秘 ',
            'image' => '/static/img/ad/god/act/science_class.jpg',
            'type' => 'null',
            'address' => '新科技馆',
            'total' => 118,
            'age' => '4岁以上',
            'rate' => 92,
            'act_id' => 0,
            'comment' => array(
                '不用排队，太开心了',
                '工作人员讲解得非常好',
                '孩子听得也认真'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '葡萄采摘，创意水果游乐园',
            'name' => '葡萄采摘，创意水果游乐园',
            'image' => '/static/img/ad/god/act/pick_grape.jpg',
            'type' => 'null',
            'address' => '七彩龙珠',
            'total' => 117,
            'age' => '3岁以上',
            'rate' => 90,
            'act_id' => 0,
            'comment' => array(
                '葡萄真的很甜，个头',
                '认识很多新的小伙伴',
                '美好的一天'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '小小眼科医生',
            'name' => '小小眼科医生',
            'image' => '/static/img/ad/god/act/eye_dc.jpg',
            'type' => 'null',
            'address' => '眼科医院',
            'total' => 96,
            'age' => '6—10岁',
            'rate' => 93,
            'act_id' => 0,
            'comment' => array(
                '学到护眼知识',
                '让孩子知道保护视力的重要性',
                '非常有意义'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '涨知识|化石小猎人，寻宝+火山爆发',
            'name' => '涨知识|恐龙寻宝+挖掘化石',
            'image' => '/static/img/ad/god/act/stone_hunter.jpg',
            'type' => 'null',
            'address' => '中华奇石馆',
            'total' => 58,
            'age' => '6—10岁',
            'rate' => 93,
            'act_id' => 0,
            'comment' => array(
                '孩子们对恐龙模型很感兴趣',
                '玩得开心，还增长知识',
                '还送了挖掘出的小恐龙骨架'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '趣味逛博物馆：解谜/寻宝/学知识',
            'name' => '城会玩|解密|寻宝|学知识城会玩丨解谜/寻宝/学知识，博物馆还能这么玩',
            'image' => '/static/img/ad/god/act/jhg_muse.png',
            'type' => 'null',
            'address' => '江汉关',
            'total' => 48,
            'age' => '5—10岁',
            'rate' => 90,
            'act_id' => 0,
            'comment' => array(
                '虽然是武汉人俺也是第一次来玩',
                '孩子边玩边学很有意义',
                '孩子们争先恐后地答题'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '涨知识|徒步武大—我眼中的武大',
            'name' => '涨知识|徒步武大—我眼中的武大',
            'image' => '/static/img/ad/god/act/wh_university.jpg',
            'type' => 'null',
            'address' => '武汉大学',
            'total' => 45,
            'age' => '5—10岁',
            'rate' => 92,
            'act_id' => 0,
            'comment' => array(
                '培养团队精神',
                '孩子满意',
                '带队老师辛苦了'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '电力博物馆：探究“十万伏特”之谜',
            'name' => '电力博物馆涨知识丨探究“十万伏特”之谜',
            'image' => '/static/img/ad/god/act/elec_muse.jpg',
            'type' => 'null',
            'address' => '武汉大学',
            'total' => 41,
            'age' => '5—10岁',
            'rate' => 86,
            'act_id' => 0,
            'comment' => array(
                '土豆都可以发电！！！',
                '激发了孩子的好奇心',
                '朋友圈里好多人问我怎么参加的'
                ),
                );
                $result[] = $item;

                $item = array(
            'title' => '职业体验|小小邮递员的成长之旅',
            'name' => '职业体验|小小邮递员的成长之旅',
            'image' => '/static/img/ad/god/act/young_postman.png',
            'type' => 'null',
            'address' => '邮局',
            'total' => 35,
            'age' => '5—10岁',
            'rate' => 80,
            'act_id' => 116,
            'comment' => array(
                '孩子第一次写信，第一次送信',
                '孩子们都没有收到过信，写写信挺好玩的',
                ),
                );
                $result[] = $item;

                return $result;
    }

    /**
     * 获取所有积分记录
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-10-24 上午11:56:20
     */
    public static function getAllLotteryScoreRecord($lottery_id, $user_id, $type = 'game'){
        $user_id = intval($user_id);
        $sql = "select * from ps_lottery_score_record
                where lottery_id = {$lottery_id}
                and user_id = {$user_id}
                and type = '{$type}'
                order by id desc";

        $result = Yii::app()->db->createCommand($sql)->queryAll();

        return $result;
    }

    /*当天玩游戏的次数*/
    public static function getLotteryScoreRecordTotal($lottery_id, $user_id, $date){
        $user_id = intval($user_id);
        $sql = "select count(*) from ps_lottery_score_record
                where lottery_id = {$lottery_id}
                and user_id = {$user_id}
                and log_date = '{$date}'
                and type = 'game'
                and score>=0
                order by id desc";        //加入了 score>=0 不加会将抽奖次数算入游戏次数 导致防作弊功能失效
        $result = Yii::app()->db->createCommand($sql)->queryScalar();

        return $result;
    }

    //获取所有积分总和
    public static function getLotteryScoreTotal($lottery_id, $user_id){
        $user_id = intval($user_id);
        $sql = "select sum(score) from ps_lottery_score_record
                where lottery_id = {$lottery_id}
                and user_id = {$user_id}";

        $result = Yii::app()->db->createCommand($sql)->queryScalar();
        return $result;
    }

    //获取虚拟用户
    public  static  function  getVirtualUser(){ 
        $sql = "SELECT
                    USER .username,
                    MOD (USER .uid, 20) AS mod_num
                FROM
                    play_user AS USER
                WHERE
                    is_vir = 1";                                        //取模数量根据奖池数量决定
        $result = Yii::app()->db->createCommand($sql)->queryAll();

        return $result;

    }
    
    //设定虚拟奖品池
    public static function getVirtualGift(){   //lottery/6 虚拟奖品池 如需复用请更换虚拟奖品信息
        return array(
            '价值80元亲子游1次',
            '1元现金红包',
            '2元现金红包',
            '5元现金红包',
            '10元现金红包',
            '20元现金红包',
            '1元遛娃活动通用券',
            '2元遛娃活动通用券',
            '3元遛娃活动通用券',
            '5元遛娃活动通用券',
            '8元遛娃活动通用券',
            '10元遛娃活动通用券',
            '20元遛娃活动通用券',
            '1元全场商品通用券',
            '2元全场商品通用券',
            '5元全场商品通用券',
            '极乐汤入场券',
            '秒杀券',
            '99元奥山滑冰新年礼卡',
            '几古家单次体验卡',
        );
    }
}