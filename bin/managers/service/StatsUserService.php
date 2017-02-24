<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/19
 * Time: 10:17
 */

class StatsUserService extends Manager{

    public function getWechatLoginUser(){
        $sql = "select count(uid) as num from play_user where login_type='weixin' or login_type='weixin_wap'";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function getAppLoginUser(){
        $sql = "select count(uid) as num from play_user where login_type!='weixin' and login_type!='weixin_wap'";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function getAllLoginUser(){
        $sql = "select count(uid) as num from play_user";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function getWithoutPhoneUser(){
        $sql = "select count(uid) as num from play_user where phone=null or phone=''";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function getOneUserManyWechat(){
        $sql = "select count(id) as num from play_user_weixin group by uid having num > 1";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public function getOneWechatManyUser(){
        $sql = "select count(id) as num from play_user_weixin group by unionid having num > 1";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public function getOneWechatManyPhone(){
        $sql = "select count(w.id) as num 
                from play_user_weixin as w, play_user as u
                where w.uid = u.uid 
                and u.phone != ''
                and u.phone != null
                group by w.uid having num > 1";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public function getOnePhoneManyWechat(){
        $sql = "select count(w.id) as num 
                from play_user_weixin as w, play_user as u
                where w.uid = u.uid 
                and u.phone != ''
                and u.phone != null
                group by w.unionid having num > 1";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public function getOnePhoneOneWechat(){
        $sql = "select count(*) as num 
                from play_user as u, play_user_weixin as w 
                where u.uid = w.uid
                and u.phone != ''
                and u.phone != null";
        return Yii::app()->db->createCommand($sql)->queryRow();
    }

    public function getRepeatPhone(){
        $sql = "select count(uid) as num, phone from play_user 
                where phone !=''
                and phone != null
                group by phone having num > 1";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }
//
//    public function getWithoutPhoneUser(){
//        $sql = "select count(uid) as num from play_user where phone=''";
//        return Yii::app()->db->createCommand($sql)->queryRow();
//    }
}