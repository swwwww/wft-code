<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/11/14
 * Time: 15:28
 */
class ShareLib extends Manager{
    // 支付成功分享
    public static function payShare()
    {
        $url = '/pay/index/share';
        return HttpUtil::setP(null, $url);
    }
}