<?php
/**
 * Created by IntelliJ IDEA.
 * User: deyi
 * Date: 2016/8/20
 * Time: 10:18
 */

class RecommendLib extends Manager{
    /*首页数据*/
    public static function choiceList($in = null){
        $url = '/coupon';
        return HttpUtil::setP($in, $url);
    }

    /*搜索首页*/
    public static function searchIndex($in = null){
        $url ='/search/index/hotpush';
        return HttpUtil::setP($in,$url);
    }

    /*搜索详情*/
    public static function searchDetail($in = null){
        $url = '/search/index/supersearch';
        return HttpUtil::setP($in,$url);
    }

    /*首页弹窗提醒*/
    public static function firstAlert($in = null){
        $url = '/application/index/firstalert';
        return HttpUtil::setP($in,$url);
    }
}