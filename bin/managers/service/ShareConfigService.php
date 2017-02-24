<?php
/**
 * 分享配置
 * @classname: ShareConfigService
 * @author 11942518@qq.com | quenteen
 * @date 2016-10-25
 */
class ShareConfigService extends Manager{

    public $lottery_id;

    public function __construct($lottery_id){
        $this->lottery_id = $lottery_id;
    }

    //lottery_id = 1
    public function getShareConfigForHolidayAd($user_id){
        $app_message_arr = array(
        0 => array(
                'title'   => '暑假不再家里蹲！花样遛娃，好玩还能涨知识！',
                'message' => '玩翻天遛娃活动火爆来袭！免费游玩门票、海量红包等你抢！',
        ),
        1 => array(
                'title'   => '玩翻天遛娃学院全新上线！海量红包领不停！',
                'message' => '暑假带娃去哪玩？火爆遛娃活动，让娃在玩耍中成长',
        ),
        2 => array(
                'title'   => '暑假跟着玩翻天出发吧，专业老师带伢玩！',
                'message' => '一大波好玩、又能涨知识的活动，正在开抢！免费门票、海量红包在等你',
        ),
        3 => array(
                'title'   => '我领到免费游玩券了！还有海量红包等你领！',
                'message' => '让孩子暑假不再家里蹲！好玩还能涨知识！玩翻天遛娃学院全新上线',
        ),

        4 => array(
                ////////////////////////////////////////////////分享文案
        ),
        );

        $time_line_arr = array(
            '暑假不再让娃“家里蹲”！玩翻天遛娃学院全新上线！免费游玩门票、火爆遛娃活动来袭',
            '暑假，要花样遛娃，好玩还能涨知识！玩翻天遛娃活动火爆开抢中!',
        );

        $app_message = $app_message_arr[rand(0, 3)];
        $time_line = $time_line_arr[rand(0, 1)];

        $base_host = HttpUtil::getBaseHost();
        $img_url = $base_host . '/static/img/market/ad/wechat_share.png';
        $link = $base_host . '/lottery/market/lottery_id/' . $this->lottery_id . '/share_user_id/' . $user_id;

        $wechat_share = $this->getShareResult($app_message, $time_line, $link, $img_url);

        return $wechat_share;
    }

    public function getShareConfigForBoyGodAd($user_id, $vote_id){
        $app_message_arr = array(
        0 => array(
                'title'   => '十一去带娃去哪玩？!海量红包热抢中！',
                'message' => '妈妈们最爱的男神，正在派送海量红包！快来选男神抢红包！',
        ),
        1 => array(
                'title'   => '十一去带娃去哪玩？!海量红包热抢中！',
                'message' => '谁是遛娃界第一男神？黄磊、杨洋、还是胡歌？快来投票选男神，抢红包',
        ),
        2 => array(
                'title'   => '我抢到20元遛娃红包！十一出游正好用呢！',
                'message' => '十一带娃去哪玩？嫌人多、远、折腾？！玩翻天遛娃师带队，十一带娃轻松好玩！',
        ),
        3 => array(
                'title'   => '十一带娃出去玩，就靠这些红包啦！',
                'message' => '妈妈们最爱的男神，正在派送海量红包！遛娃就要好玩又炫酷！',
        ),
        );

        $time_line_arr = array(
            '十一去带娃去哪玩？!海量红包热抢中！',
            '抢到20元遛娃红包！十一出游正好用呢！',
        );

        $app_message = $app_message_arr[rand(0, 3)];
        $time_line = $time_line_arr[rand(0, 1)];

        $base_host = HttpUtil::getBaseHost();
        $img_url = $base_host . "/static/img/ad/god/share_red_cash.png";
        if($vote_id == null || $vote_id == 0){
            $link = $base_host . '/lottery/market/lottery_id/' . $this->lottery_id . '/share_user_id/' . $user_id;
        }else{
            $link = $base_host . '/lottery/friend/lottery_id/' . $this->lottery_id . '/vote_id/' . $vote_id;
        }

        $wechat_share = $this->getShareResult($app_message, $time_line, $link, $img_url);

        return $wechat_share;
    }

    public function getShareConfigForGeeguAd($user_id){
        $app_message_arr = array(
        0 => array(
                'title'   => '请10000组家庭带娃免费玩！武汉超大手笔开业福利仅此1次，还不快抢？',
                'message' => '超长室内滑滑梯、20000册全球珍稀绘本、1600辆进口汽车模型……等你来耍',
        ),
        1 => array(
                'title'   => '壕|这10000张有钱也买不到的珍贵体验券玩游戏就送，全武汉宝妈抢疯了',
                'message' => '超长室内滑滑梯、20000册全球珍稀绘本、1600辆进口汽车模型……等你来耍',
        ),
        2 => array(
                'title'   => '武汉95%的宝妈都在玩，1万份双11大礼等你瓜分#几古的家开业狂欢送#',
                'message' => '没时间解释了，就是现在，马上点开',
        ),
        3 => array(
                'title'   => '双11给宝宝的绝赞礼物限时免费送，全武汉爸妈都在抢，速来！',
                'message' => '没时间解释了，就是现在，马上点开',
        ),
        );

        $time_line_arr = array(
            '壕|这10000张有钱也买不到的珍贵体验券玩游戏就送，全武汉宝妈抢疯了',
            '双11给宝宝的绝赞礼物限时免费送，全武汉爸妈都在抢，速来！',
        );

        $app_message = $app_message_arr[rand(0, 3)];
        $time_line = $time_line_arr[rand(0, 1)];

        $base_host = HttpUtil::getBaseHost();
        $img_url = $base_host . '/static/img/ad/grasp/jg-share.jpg';

        $link = $base_host . '/lottery/market/lottery_id/' . $this->lottery_id . '/share_user_id/' . $user_id;

        $wechat_share = $this->getShareResult($app_message, $time_line, $link, $img_url);

        return $wechat_share;
    }

    public function getShareConfigForTaobaoSale($user_id){
        $choose_city = StringUtil::getCustomCity();

        if($choose_city == 'nanjing'){
            $app_message_arr = array(
            0 => array(
                'title'   => '我领到玩翻天双11红包啦！快来抢',
                'message' => '玩翻天双11，遛娃达人狂欢啦！全场1元起，充200送200！',
            ),
            1 => array(
                'title'   => '我领到玩翻天双11红包啦！快来抢',
                'message' => '玩翻天双11，遛娃达人狂欢啦！全场1元起，充200送200！',
            ),
            );
            $time_line_arr = array(
                '我领到玩翻天双11红包啦！快来抢',
                '我领到玩翻天双11红包啦！快来抢',
            );
        }else{
            $app_message_arr = array(
            0 => array(
                'title'   => '我领到玩翻天双11红包啦！快来抢',
                'message' => '玩翻天双11，遛娃达人狂欢啦！全场1折起，充500送420！',
            ),
            1 => array(
                'title'   => '玩翻天双11！儿童乐园、游泳馆1元起！',
                'message' => '遛娃达人们，开始狂欢啦~红包、充值返现、超值门票，手慢无哦',
            ),
            );

            $time_line_arr = array(
                '我领到玩翻天双11红包啦！快来抢',
                '玩翻天双11！儿童乐园、游泳馆1元起！',
            );
        }

        $app_message = $app_message_arr[rand(0, 1)];
        $time_line = $time_line_arr[rand(0, 1)];

        $base_host = HttpUtil::getBaseHost();
        $img_url = $base_host . '/static/img/ad/god/share_red_cash.png';

        $link = $base_host . '/lottery/market/lottery_id/' . $this->lottery_id . '/share_user_id/' . $user_id;

        $wechat_share = $this->getShareResult($app_message, $time_line, $link, $img_url);

        return $wechat_share;
    }

    public function getShareConfigForVip($user_id, $is_lottery = 0){
        $vip_share = ShareLib::payShare();
        $share_data = $vip_share['data'];

        $app_message = array(
            'title' => $share_data['share_title'],
            'message' => $share_data['share_content'],
        );
        $time_line = $share_data['share_content'];
        $link = $share_data['share_url'];
        $img_url = $share_data['share_img'];

        $wechat_share = $this->getShareResult($app_message, $time_line, $link, $img_url);

//        $base_host = HttpUtil::getBaseHost();
//        if($user_id > 0){
//            $lottery_url = $base_host . '/member/guide/share_user_id/' . $user_id;
//        }else{
//            $lottery_url = $base_host . '/member/guide';
//        }

        $wechat_share['is_lottery'] = $is_lottery;
        $wechat_share['lottery_url'] = $share_data['jump_url'];

        return $wechat_share;
    }

    public function getShareConfigForBattle($user_id,$user_name){

        if(StringUtil::checkPhone($user_name)){
            $user_name=StringUtil::processPhone($user_name);
        }

        $app_message_arr = array(
            0 => array(
                'title'   => "你的好友{$user_name}正在抓金鸡，速来围观",
                'message' => '玩太空飞鸡战，拿玩翻天百万新年礼物',
        ),
            1 => array(
                'title'   => 'My friend，我在玩太空飞鸡战，快来一起玩翻天！',
                'message' => '玩太空飞鸡战，拿玩翻天百万新年礼物',
            ),
            2 => array(
                'title'   => '我去，这个小游戏太好玩了，快来看看！',
                'message' => "您的好友{$user_name}正在玩太空飞鸡战，快来加入战队",
            ),
            3 => array(
                'title'   => '玩翻天的新年礼物，你要不要？',
                'message' => "你的好友{$user_name}送你一份新年礼物，快来领取",
            ),
        );

        $time_line_arr = array(
            'My friend，我在玩太空抓金鸡，快来一起玩翻天！',
            '我去，这个小游戏太好玩了，快来看看！',
            '玩翻天的新年礼物，你要不要？',
            "你的好友{$user_name}正在抓金鸡，速来围观！",
        );

        $app_message = $app_message_arr[rand(0, 3)];
        $time_line = $time_line_arr[rand(0, 3)];

        $base_host = HttpUtil::getBaseHost();

        $img_url = $base_host . '/static/img/ad/battle/js-share-one.jpg';                       //todo

        $link = $base_host . '/lottery/market/lottery_id/' . $this->lottery_id . '/share_user_id/' . $user_id;

        $wechat_share = $this->getShareResult($app_message, $time_line, $link, $img_url);

        return $wechat_share;
    }
    /**
     * @param $app_message array 分享给朋友的标题的内容
     * @param $time_line  string 分享到朋友圈的标题
     * @param $link
     * @param $img_url
     *
     * @return array
     * author: MEX | mixmore@yeah.net
     */
    public function getShareResult($app_message, $time_line, $link, $img_url){
        $wechat_service = new WechatConfigService();

        $wechat_share = array(
            'ticket' => $wechat_service->getSignature(),
            'share_app_message' => array(
                'title'   => $app_message['title'],
                'message' => $app_message['message'],
                'link'    => $link,
                'img_url' => $img_url,),
            'share_time_line'   => array(
                'title'   => $time_line,
                'link'    => $link,
                'img_url' => $img_url,),
        );

        return $wechat_share;
    }
}