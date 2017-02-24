<?php
/**
 * 用户数据层
 * @classname: UserData
 * @author 11942518@qq.com | quenteen
 * @date 2016-7-2
 */
class UserAssociateData extends Manager{

    /**
     * 获取出行人详细信息
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-7-16 下午06:04:20
     */
    public static function getAssociateById($id){
        $user_assoc_vo = UserAssociatesVo::model()->find('associates_id = :associates_id', array(':associates_id' => $id));

        if($user_assoc_vo){
            $user_assoc_vo = $user_assoc_vo->getAttributes();
        }

        return $user_assoc_vo;
    }

    //获取baby信息
    public static function getBabyInfoById($id){
        $user_baby_vo = UserBabyVo::model()->find('id = :id', array(':id'=>$id));

        if($user_baby_vo){
            $user_baby_vo = $user_baby_vo->getAttributes();
        }
        return $user_baby_vo;
    }


}