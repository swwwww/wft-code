<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 3:39
 */

class AskLib extends Manager{

    //遛娃活动咨询接口
    public function PlayAsk($in){
        $url = '/kidsplay/consult';
        return HttpUtil::setP($in, $url);
    }

    //遛娃活动咨询列表接口
    public function PlayAskList($in){
        $url = '/kidsplay/consult/list';
        return HttpUtil::setP($in, $url);
    }
    
}