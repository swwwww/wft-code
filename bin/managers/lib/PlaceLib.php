<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 上午 10:46
 */
class PlaceLib extends Manager
{
    //游玩地数据接口
    public function PlaceIndex($in){
        $url = '/place/index';
        return HttpUtil::setP($in, $url);
    }

}