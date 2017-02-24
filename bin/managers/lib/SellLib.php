<?php

/**
 * 分销接口
 * @classname: SellLib
 * @author 11942518@qq.com | quenteen
 * @date 2017-1-9
 */
class SellLib extends Manager
{
    //活动列表数据接口  //行程安排及往期回顾
    public static function shareClick($in)
    {
        $url = '/user/sell/shareClick';
        return HttpUtil::setP($in, $url);
    }
}