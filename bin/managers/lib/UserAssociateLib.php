<?php

class UserAssociateLib extends Manager{

    //活动所有用户信息
    public function getAllUserAssociate($in = null){
        $url = '/user/phone';
        return HttpUtil::setP($in, $url);
    }

    //获得所有出行人
    public function getAllTravellerAssociate($in = null){
        $url = '/user/associates/list';
        return HttpUtil::setP($in, $url);
    }

    //获取baby信息
    public function getAllBabyAssociate($in = null){
        $url = '/user/info';
        return HttpUtil::setP($in, $url);
    }
}

