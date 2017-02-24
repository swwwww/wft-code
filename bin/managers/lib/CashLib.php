<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/11/14
 * Time: 16:04
 */
class CashLib extends Manager{
    // 免费玩资格券
    public static function getFreeCoupon()
    {
        $url = '/user/index/getFreeCoupon';
        return HttpUtil::setP(null, $url);
    }
}