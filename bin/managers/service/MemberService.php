<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/11/19
 * Time: 13:20
 */
class MemberService extends Manager
{
    public static function getVipNum()
    {
        $sql = "select count(*) as num from play_member where member_level>0";
        $res = Yii::app()->db->createCommand($sql)->queryRow();
        return floor(($res['num']/3) * 7);  /*虚拟数据*/
    }

    // checkout界面进行vip参数的判断
    public static function getVipParams($members, $most_free_buy_same_day_number, $my_free_coupon_number, $charges = null)
    {
        $member = null;
        for ($i = 0; $i < count($members); $i++) {
            if ($charges != null && $charges[$i]['id'] == $members[$i]['id']) {
                // 首次下单的时候
                $members[$i]['buy_number']      = $charges[$i]['buy_number'];
                $members[$i]['free_buy_number'] = $charges[$i]['free_buy_number'];
            }

            $temp_member = $members[$i];
            if ($temp_member['buy_number'] <= 0) { // 该收费项没有购买，如果 members 传过来的时候已经过滤了 buy_number == 0 的，就不需要判断
                continue;
            }
            if ($temp_member['need_free_coupon_number'] <= 0) { // 不是免费玩的忽略
                continue;
            }
            if ($temp_member['free_buy_number'] > 0) { // 如果有用免费玩资格券兑换的，就取该收费项，退出 for 循环
                $member = $temp_member;
                break;
            }
            $member = $member != null ? $member : $temp_member; // 取第一个免费玩收费项

        }
        return self::checkoutStandMessage($member, $most_free_buy_same_day_number, $my_free_coupon_number);
    }

    public static function getPlaySelectVipShow($res, $most_free_buy_same_day_number, $my_free_coupon_number)
    {
        // 会员购买资格的判断
        for ($i = 0; $i < count($res['members']); $i++) {
            if ($res['members'][$i]['need_free_coupon_number'] <= 0) { // 该收费项不能用免费玩资格券兑换
                $show_free_bar = 0;
            } else if ($res['members'][$i]['free_number'] > 0 && $res['members'][$i]['free_used_number'] >= $res['members'][$i]['free_number']) {  // 该收费项的会员免费玩名额有限制并且会员免费玩名额已满
                $show_free_bar = 0;
            } else if ($res['members'][$i]['my_free_buy_number'] >= $res['members'][$i]['most_free_buy_number']) { // 已用免费玩资格券兑换过该收费项
                $show_free_bar = 0;
            } else if ($res['members'][$i]['my_free_buy_same_day_number'] >= $most_free_buy_same_day_number) { // 已兑换过活动参加那天的其它活动了
                $show_free_bar = 0;
            } else if ($res['members'][$i]['need_free_coupon_number'] > $my_free_coupon_number) { // 免费玩资格券数量不足
                $show_free_bar = 0;
            } else { // 可以用免费玩资格券兑换
                $show_free_bar = 1;
            }
            $res['members'][$i]['show_free_bar'] = $show_free_bar;
        }
        return $res;
    }

    public static function checkoutStandMessage($member, $most_free_buy_same_day_number, $my_free_coupon_number)
    {
        $message       = '';
        $show_free_bar = true;
        $show_free_bar_jump = false;
        if ($member == null) {
            $show_free_bar = false;
        } else if ($member['free_buy_number'] > 0) { // 用免费玩资格券兑换的次数大于 0
            $message = '消耗 ' . $member['need_free_coupon_number'] . ' 次，省 ' . $member['price'];
        } else if ($member['free_number'] > 0 && $member['free_used_number'] >= $member['free_number']) { // 会员免费玩名额有限制并且会员免费玩名额已满
            $show_free_bar = false;
        } else if ($member['my_free_buy_number'] >= $member['most_free_buy_number']) {
            $message = '已兑换过这场活动了';
        } else if ($member['my_free_buy_same_day_number'] >= $most_free_buy_same_day_number) {
            $message = '已兑换过今天其它活动了';
        } else if ($member['need_free_coupon_number'] > $my_free_coupon_number) {
            $show_free_bar_jump = true;
            $message = '次数不足，去获取';
        } else {
            $message = '还有' . $my_free_coupon_number . '次免费资格';
        }
        $checkout_stand_message['message']       = $message;
        $checkout_stand_message['show_free_bar'] = $show_free_bar;
        $checkout_stand_message['show_free_bar_jump'] = $show_free_bar_jump;
        return $checkout_stand_message;
    }

    //传递客服系统消息
    public static function postServiceInfo($res, $type, $id){

        $service_info = array();
        $service_info['product_type'] = $type;
        $service_info['id'] = $id;


        if($type == 2 || $type == 4){
            $service_info['product_price'] = $res['price'];
        }else if($type == 1){
            $service_info['product_price'] = $res['low_price'];
        }else if($type == 3){
            $service_info['product_price'] = $res['money'];
        }

        if($type == 1 || $type == 2){
            $service_info['share_title'] = $res['share_title'];
            $service_info['share_content'] = $res['share_content'];
            $service_info['share_image'] = $res['share_img'];
            $service_info['share_url'] = $res['share_url'];
        }else{
            $service_info['share_title'] = $res['order_title'];
            $service_info['share_content'] = $res['order_content'];
            $service_info['share_image'] = $res['order_image'];
            $service_info['share_url'] = $res['order_url'];
        }

        return $service_info;
    }

    // 会员引导页的数据
    public static function guidePageInfo()
    {
        $goods = array(
        2  => array(
                'name'    => '孩子王童乐园',
                'info'    => '独家|全网最优惠',
                'price'   => '29',
                'sold'    => '3654',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '19.png'
                ),
                0  => array(
                'name'    => '海昌极地海洋公园',
                'info'    => '年卡续卡 仅在玩翻天',
                'price'   => '400',
                'sold'    => '25361',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '27.png'
                ),
                1  => array(
                'name'    => '悠游堂',
                'info'    => '主题式亲子乐园，好玩',
                'price'   => '9.9',
                'sold'    => '19837',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '31.png'
                ),
                13 => array(
                'name'    => '万达儿童乐园',
                'info'    => '游戏币5.1折，玩得更爽',
                'price'   => '200',
                'sold'    => '15927',
                'is_logo' => '',
                'status'  => '1',
                'img'     => '16.png'
                ),
                3  => array(
                'name'    => '欢乐谷',
                'info'    => '亲子年卡|一年无限畅玩',
                'price'   => '620',
                'sold'    => '598',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '25.png'
                ),
                4  => array(
                'name'    => '贝乐园',
                'info'    => '8店通用，小宝宝必入',
                'price'   => '9.9',
                'sold'    => '9152',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '30.png'
                ),
                5  => array(
                'name'    => '幻贝家',
                'info'    => '在这里，有1000种玩法',
                'price'   => '99',
                'sold'    => '16351',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '20.png'
                ),
                6  => array(
                'name'    => '武汉冰龙国际运动俱乐部（原奥山欧悦）',
                'info'    => '经典|奥山冰雪主题',
                'price'   => '399',
                'sold'    => '235',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '26.png'
                ),
                7  => array(
                'name'    => '全明星滑冰俱乐部',
                'info'    => '新晋滑冰好玩地',
                'price'   => '50',
                'sold'    => '397',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '17.png'
                ),
                8  => array(
                'name'    => '星期8小镇',
                'info'    => '独家特价|1大1小仅64.5',
                'price'   => '387',
                'sold'    => '8612',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '22.png'
                ),
                9  => array(
                'name'    => '松松小镇',
                'info'    => '武汉的迪士尼，畅玩1元起',
                'price'   => '1',
                'sold'    => '365',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '24.png'
                ),
                10 => array(
                'name'    => '黄石兰博基尼亲子度假酒店',
                'info'    => '超火爆|亲子度假必选',
                'price'   => '869',
                'sold'    => '913',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '18.png'
                ),
                11 => array(
                'name'    => '海豚儿童书店',
                'info'    => '图书有价，亲子阅读无价',
                'price'   => '83',
                'sold'    => '135',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '29.png'
                ),
                12 => array(
                'name'    => '悦兮半岛温泉度假酒店',
                'info'    => '温泉成人票，低于8折',
                'price'   => '158',
                'sold'    => '491',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '21.png'
                ),
                14 => array(
                'name'    => '春泉庄温泉度假酒店',
                'info'    => '虚浮|泡温泉+5星级亲子房',
                'price'   => '738',
                'sold'    => '249',
                'is_logo' => '1',
                'status'  => '1',
                'img'     => '23.png'
                )
                );
                return $goods;
    }

    public static function guideSecondPage()
    {
        $goods = array(
        0  => array(
                'name'      => '海昌极地海洋公园',
                'info'      => '极地成人单次门票 8.3折！',
                'price'     => '125',
                'old_price' => '150',
                'sold'      => '2248',
                'id'        => '1078',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '1101.jpg'
                ),
                1  => array(
                'name'      => '孩子王童乐园',
                'info'      => '孩子王单次门票 仅38元',
                'price'     => '38',
                'old_price' => '79',
                'sold'      => '33',
                'id'        => '1802',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '502.jpg'
                ),
                2  => array(
                'name'      => '幻贝家',
                'info'      => '幻贝家2大1小单次票 6折',
                'price'     => '138',
                'old_price' => '230',
                'sold'      => '2466',
                'id'        => '1829',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '602.jpg'
                ),
                3  => array(
                'name'      => '海昌极地海洋公园',
                'info'      => '极地|单人门票150，2大1小家庭年卡仅499',
                'price'     => '499',
                'old_price' => '798',
                'sold'      => '259',
                'id'        => '1987',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '1202.jpg'
                ),
                4  => array(
                'name'      => '贝乐园',
                'info'      => '贝乐园年底钜惠 1元起',
                'price'     => '1',
                'old_price' => '20',
                'sold'      => '22',
                'id'        => '1955',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '1401.jpg'
                ),
                5  => array(
                'name'      => '松松小镇',
                'info'      => '松松小镇游玩1元起',
                'price'     => '1',
                'old_price' => '31',
                'sold'      => '441',
                'id'        => '1890',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '1002.jpg'
                ),
                6  => array(
                'name'      => '悦兮半岛温泉度假酒店',
                'info'      => '悦兮半岛温泉成人票，低于8折',
                'price'     => '158',
                'old_price' => '198',
                'sold'      => '22',
                'id'        => '1939',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '702.jpg'
                ),
                7  => array(
                'name'      => '武汉冰龙国际运动俱乐部（原奥山欧悦）',
                'info'      => '奥山专业滑冰课程，包教会',
                'price'     => '399',
                'old_price' => '599',
                'sold'      => '22',
                'id'        => '2086',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '402.jpg'
                ),
                8  => array(
                'name'      => '星期8小镇',
                'info'      => '【独家】星期8小镇6次卡，1大1小单次仅64.5',
                'price'     => '387',
                'old_price' => '580',
                'sold'      => '63',
                'id'        => '2035',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '801.jpg'
                ),
                9  => array(
                'name'      => '黄石兰博基尼亲子度假酒店',
                'info'      => '兰博基尼大床房/双床房',
                'price'     => '869',
                'old_price' => '1099',
                'sold'      => '286',
                'id'        => '1390',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '302.jpg'
                ),
                10 => array(
                'name'      => '贝乐园',
                'info'      => '贝乐园单次游玩券',
                'price'     => '39.8',
                'old_price' => '60',
                'sold'      => '31',
                'id'        => '1951',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '1502.jpg'
                ),
                11 => array(
                'name'      => '欢乐谷',
                'info'      => '欢乐谷|单人门票要200，亲子年卡仅620！',
                'price'     => '620',
                'old_price' => '985',
                'sold'      => '47',
                'id'        => '1997',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '1302.jpg'
                ),
                12 => array(
                'name'      => '全明星滑冰俱乐部',
                'info'      => '全明星滑冰双人票 人均55.5',
                'price'     => '111',
                'old_price' => '180',
                'sold'      => '52',
                'id'        => '1824',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '201.jpg'
                ),
                13 => array(
                'name'      => '万达儿童乐园',
                'info'      => '万达儿童乐园游戏币 5.1折',
                'price'     => '200',
                'old_price' => '390',
                'sold'      => '770',
                'id'        => '977',
                'is_logo'   => '',
                'status'    => '1',
                'img'       => '101.jpg'
                ),
                14 => array(
                'name'      => '春泉庄温泉度假酒店',
                'info'      => '虚浮|春泉庄温泉+华美达亲子房+双早',
                'price'     => '738',
                'old_price' => '1099',
                'sold'      => '22',
                'id'        => '2084',
                'is_logo'   => '1',
                'status'    => '1',
                'img'       => '901.jpg'
                )
                );
                return $goods;
    }

    public function checkInnerPlayer($user_id){
        $user_list = array(
                '|客服组|刘艳|' => 155005,
                '|客服组|江晨曦|' => 195890,
                '|客服组|罗露|' => 157063,
                '|客服组|舒榕|' => 13160,
                '|渠道组|苏娟|' => 41376,
                '|渠道组|徐晶|' => 202650,
                '|渠道组|王炳臣|' => 195174,
                '|编辑组|余淑君|' => 54399,
                '|编辑组|毕博采|' => 151996,
                '|编辑组|方丽娟|' => 10019,
                '|商务|李娜|' => 144861,
                '|商务|彭琳|' => 31336,
                '|推广|王双丽|' => 10007,
                '|推广|梁冰雪|' => 157675,
                '|商务|王炜|' => 44982,
                '|商务|张琦|' => 47430,
                '|商务|胡力'=>218898,
                '|人力资源部|李阳|' => 14893,
                '|人力资源部|卢倩|' => 170399,
                '|策划|冯婷婷|' => 36370,
                '|策划|朱敏|' => 166808,
                '|策划|龙凤|' => 150276,
                '|策划|李美姗|' => 160707,
                '|策划|楚媛|' => 160882,
                '|策划|刘江河|' => 169302,
                '|策划|高怡婧|' => 199043,
                '|品控|黄晶|' => 158579,
                '|品控|徐娜|' => 150188,
                '|品控|谢雪婷|' => 219127,
                '|品控|程王丽|' => 190250,
                '|行政|张辛|' => 40177,
                '|行政|范鹏|' => 195128,
                '|遛娃师|陈必诚|' => 85899,
                '|遛娃师|郑炜|' => 168276,
                '|遛娃师|周玥|' => 219126,
                '|遛娃师|许博文|' => 202936,
                '|遛娃师|明志远|' => 204534,
                '|遛娃师|李小卫|' => 207244,
                '|遛娃师|王起明|' => 218757,
                '|线上运营|朱佳琪|' => 30218,
                '|技术|沈伟|' => 142919,
                '|技术|林飞浪|' => 179497,
                '|技术|王维杰|' => 10023,
                '|技术|李佳|' => 39125,
                '|技术|毕明君|' => 169280,
                '|技术|陈铁|' => 23874,
                '|技术|钟加仁|' => 23989,
                '|技术|万江|' => 10013,
                '|技术|周贝|' => 137151,
                '|技术|聂锐|' => 59810,
                '|技术|徐行贤|' => 148462,
                '|技术|覃涛|' => 152636,
                '|技术|佟鑫|' => 193936,
                '|技术|代寅|' => 11771,
                '|技术|郑艳|' => 186603,
                '|总经理|陈国庆|' => 10000,
                '|运营总监|彭文|' => 10028
        );

        foreach($user_list as $key => $val){
            if($user_id == $val){
                return true;
            }
        }

        return false;
    }
}