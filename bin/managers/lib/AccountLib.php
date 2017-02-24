<?php

class AccountLib extends Manager{

    //抽奖-充值
    public static function lotteryWin($in){
        $url = '/user/account/lotteryWin';
        return HttpUtil::setP($in, $url);
    }

    public static function activityFreeCouponForLottery($in){
        $url = '/user/index/activityFreeCouponForLottery';
        return HttpUtil::setP($in, $url);
    }
}