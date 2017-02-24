<?php

class UserResource extends Resource {

    public function nowUser() {
        $user_vo = UserLib::getNowUser();

        if($user_vo){
            $user_id = $user_vo['uid'];
            $vip = MemberData::getMember($user_id);

            $user_vo['is_vip'] = 0;
            if($vip){
                $user_vo['is_vip'] = 1;
            }
        }

        $result = array(
            'user' => $user_vo,
        );
        return $result;
    }

    //获取商家用户的信息
    public function businessNowUser() {
        $user_vo = UserLib::getBusinessNowUser();

        $result = array(
            'user' => $user_vo,
        );
        return $result;
    }

}




