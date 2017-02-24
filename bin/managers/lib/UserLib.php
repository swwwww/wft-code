<?php
class UserLib extends Manager{

    //获取当前登陆用户
    public static function getNowUser(){
        if(isset($_SESSION['user'])) {
            if(MathUtil::getChance(1)){
                $uid = $_SESSION['user']['uid'];
                $user = UserData::getUserById($uid);
                $_SESSION['user'] = $user;
            }else{
                $user = $_SESSION['user'];
            }
            return $user;
        } else {
            return false;
        }
    }

    //设置当前登陆用户
    public static function setNowUser($user_vo){
        $_SESSION['user'] = $user_vo;
    }

    //获取当前管理员用户
    public static function getBusinessNowUser(){
        if(isset($_SESSION['business_user'])) {
            if(MathUtil::getChance(1)){
                $uid = $_SESSION['business_user']['id'];
                $user = UserData::getBusinessUserById($uid);
                $user['organizer_id'] = $uid;
                $user['rc_auth'] = $_SESSION['business_user']['rc_auth'];
                $_SESSION['business_user'] = $user;
            }else{
                $user = $_SESSION['business_user'];
            }
            return $user;
        } else {
            return false;
        }
    }

    //设置当前登陆用户
    public static function setBusinessNowUser($user_vo){
        $_SESSION['business_user'] = $user_vo;
    }

    //弃用 - check by qintao - 2016-11-18
    public static function setNowUserByPhone($phone){
        $flag = false;

        if(StringUtil::checkPhone($phone)){
            $target_user_id = 0;

            $now_user_vo = UserLib::getNowUser();
            $wechat_user_id = $now_user_vo['wechat_user']['id'];
            $wechat_app_user_id = $now_user_vo['wechat_user']['uid'];

            $phone_user_vo = UserData::getUserByPhone($phone);

            //存在该手机的app用户，则更新微信账号对应app用户
            if($phone_user_vo){
                $phone_user_id = $phone_user_vo['uid'];

                $target_user_id = $phone_user_id;

                $sql = "update play_user_weixin set uid = {$phone_user_id} where id = {$wechat_user_id}";
            }else{//若不存在，测将更新微信用户对应的app用户手机号
                $target_user_id = $wechat_app_user_id;

                $sql = "update play_user set phone = '{$phone}' where uid = {$wechat_app_user_id}";
            }

            $update = Yii::app()->db->createCommand($sql)->execute();

            if($update){
                $flag = true;

                $target_user_vo = UserData::getUserById($target_user_id);

                UserLib::setNowUser($target_user_vo);

                return $target_user_vo;
            }
        }

        return $flag;
    }

    //弃用 - check by qintao - 2016-11-18
    // 核对更新用户数据
    public static function checkNowUserByPhone($phone){
        if(!StringUtil::checkPhone($phone)) {
            return false;
        }

        $now_user = self::getNowUser();
        $wechat_uid = $now_user['uid']; // 当前session中的uid
        // 通过手机号读取数据库中的uid 这里根据两种情况进行判断
        $phone_user_vo = UserData::getUserByPhone($phone);
        if($phone_user_vo){
            /**
             * 解决一个app账户，被多个微信账户重复绑定的问题
             * （防止用户输错手机号，导致的串号）
             */
            $phone_user_id = $phone_user_vo['uid'];
            $phone_wechat_user_vo = WechatUserVo::model()->find("uid = :uid and login_type = 'weixin_wap'", array(':uid'=>$phone_user_id));
            //1、该手机号已经绑过微信账户，则检查当前微信和app账号是否一致
            if($phone_wechat_user_vo){
                if($wechat_uid == $phone_user_id){//当前微信账户的手机号和app一致
                    return true;
                }else{//不一致，则不能绑定，提示手机号码错误
                    return false;
                }
            }else{
                //2、该手机号还未绑定微信账户，则和当前微信账户进行绑定
                $wechat_user_vo = WechatUserVo::model()->find("uid = :uid and login_type = 'weixin_wap'", array(':uid'=>$wechat_uid));

                if(!isset($wechat_user_vo)) {
                    return false;
                }

                if($wechat_uid != $phone_user_id){
                    // 修正数据库里面的数据
                    $wechat_user_vo['uid'] = $phone_user_id;
                    $wechat_user_vo->save();
                    // 更新SESSION
                    $phone_user_vo = $phone_user_vo->getAttributes();
                    $phone_user_vo['wechat_user'] = $wechat_user_vo;
                    self::setNowUser($phone_user_vo);
                }else{
                    // 数据都存在且正确
                }
            }
        }else{
            // 数据库没有相应手机号对应用户
            $phone_user_vo = UserVo::model()->find('uid=:uid', array(':uid'=>$wechat_uid));
            $phone_user_vo['phone'] = $phone;
            $phone_user_vo->save();
            // 更新SESSION里面的手机数据
            $_SESSION['user']['phone'] = $phone;
        }

        return true;
    }

    //初始化微信端登陆或自动注册 - 设置session，并返回用户信息
    public static function initUserInfoByWechatUser($source_user, $user_access_token_arr){
        $flag = false;

        $open_id = $source_user['openid'];
        $union_id = $source_user['unionid'];

        $app_id = Yii::app()->params['wechat']['appid'];

        $wechat_user = WechatUserVo::model()->find("unionid = :union_id and appid = :app_id and login_type = 'weixin_wap'", array(
            ':union_id' => $union_id,
            ':app_id' => $app_id,
        ));

        $ps_user_vo = UserLib::getAppUserByUnionId($union_id);

        //若该微信用户存在，则真实用户一定已存在，查找该真实手机用户
        if($wechat_user){
            if($ps_user_vo){
                $flag = true;

                $app_user_id = $ps_user_vo['uid'];

                //union_id一致的用户中，存在有手机号的用户，则将该微信账户绑定到app账户
                if($app_user_id != $wechat_user['uid']){
                    $wechat_user['uid'] = $app_user_id;
                    $wechat_user->save();
                }
            }
        }else{//否则，微信用户不存在，则user和user_weixin都需要保存该用户信息
            $user_name = $source_user['nickname'];
            $sex = $source_user['sex'];
            $language = $source_user['language'];
            $city = $source_user['city'];
            $province = $source_user['province'];
            $country = $source_user['country'];
            $head_img_url = $source_user['headimgurl'];
            $privilege = $source_user['privilege'];

            $user_access_token = $user_access_token_arr['access_token'];
            $user_refresh_token = $user_access_token_arr['refresh_token'];
            $user_scope = $user_access_token_arr['scope'];

            $login_type = 'weixin_wap';

            $trans = Yii::app()->db->beginTransaction();

            try{
                $flag = true;
                if(!$ps_user_vo){
                    //1.保存play_user
                    $ps_user_vo = new UserVo();
                    $ps_user_vo->username = $user_name;
                    $ps_user_vo->token = md5(md5($open_id) . time() . rand(1, 90000));
                    $ps_user_vo->login_type = $login_type;
                    $ps_user_vo->is_online = 1;
                    $ps_user_vo->dateline = time();
                    $ps_user_vo->status = 1;
                    $ps_user_vo->img = $head_img_url;
                    $ps_user_vo->city = 'WH';

                    $ps_user_vo->save();
                }

                //2.根据play_user的uid，保存play_user_weixin
                $ps_user_id = $ps_user_vo['uid'];

                $wechat_user = new WechatUserVo();
                $wechat_user->uid = $ps_user_id;
                $wechat_user->open_id = $open_id;
                $wechat_user->unionid = $union_id;
                $wechat_user->access_token_wap = $user_access_token;
                $wechat_user->refresh_token_wap = $user_refresh_token;
                $wechat_user->login_type = $login_type;
                $wechat_user->appid = $app_id;
                $wechat_user->nickname = $user_name;
                $wechat_user->sex = $sex;
                $wechat_user->language = $language;
                $wechat_user->city = $city;
                $wechat_user->province = $province;
                $wechat_user->country = $country;

                $wechat_user->save();

                $trans->commit();
            }catch(Exception $e){
                $flag = false;
                $trans->rollback();
            }
        }

        if($flag){
            $ps_user_vo = $ps_user_vo->getAttributes();
            $ps_user_vo['wechat_user'] = $wechat_user->getAttributes();

            //3.设置用户session
            UserLib::setNowUser($ps_user_vo);

            return $ps_user_vo;
        }else{
            return $flag;
        }
    }


    //根据手机号码，初始化play_user的记录
    public static function initPlayUserByPhone($phone){
        $ps_user_vo = UserData::getUserByPhone($phone);

        if(!$ps_user_vo){
            $ps_user_vo = new UserVo();
            
            $ps_user_vo->phone = $phone;
            $ps_user_vo->token = md5(md5($phone) . time() . rand(1, 90000));
            $ps_user_vo->login_type = 'weixin_wap';
            $ps_user_vo->is_online = 1;
            $ps_user_vo->dateline = time();
            $ps_user_vo->status = 1;
            $ps_user_vo->city = 'WH';

            if(!$ps_user_vo->save()){
                return false;
            }
            //表征为新用户
            //$ps_user_vo->is_new_user = 1;
        }

        return $ps_user_vo;
    }

    /**
     * 根据union_id获取真实的app用户（有手机号的用户）
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-7-15 下午03:09:06
     */
    public static function getAppUserByUnionId($union_id){
        $all_wechat_user_arr = WechatUserVo::model()->findAll('unionid = :union_id', array(
            ':union_id' => $union_id,
        ));

        $target_user_vo = array();
        $check_flag = false;
        foreach((array)$all_wechat_user_arr as $key => $val){
            $user_id = $val['uid'];
            $login_type = $val['login_type'];

            $user_vo = UserVo::model()->find('uid = :uid', array(':uid' => $user_id));
            $phone = $user_vo['phone'];

            if($check_flag == false && $login_type == 'weixin_wap'){
                $check_flag = true;
                //取最先注册的微信用户信息
                $target_user_vo = $user_vo;
            }

            if($phone && StringUtil::checkPhone($phone)){
                $app_user_vo = $user_vo;
                return $app_user_vo;
            }
        }

        return $target_user_vo;
    }

    public static function processUserChangeLog($data, $lottery_id = 0){

        //记录用户变更日志
        $change_log_vo = new LotteryUserChangeLogVo();

        $phone = $data['phone'];
        $wechat_user_id = $data['wechat_user_id'];
        $old_user_id = $data['old_user_id'];
        $new_user_id = $data['new_user_id'];

        $change_log_vo->lottery_id = $lottery_id;
        $change_log_vo->phone = $phone;
        $change_log_vo->wechat_user_id = $wechat_user_id;
        $change_log_vo->old_user_id = $old_user_id;
        $change_log_vo->new_user_id = $new_user_id;
        $change_log_vo->created = TimeUtil::getNowDateTime();
        $change_log_vo->updated = TimeUtil::getNowDateTime();

        $flag = $change_log_vo->save();

        return $flag;
    }

    public static function checkUserPhone(){
        $user_vo = self::getNowUser();
        if (!isset($user_vo['phone']) || $user_vo['phone'] == ''){
            // 跳转到登陆界面
            $base_host = HttpUtil::getBaseHost();

            $callback_url = $base_host . $_SERVER['REQUEST_URI'];
            $auth_url = $base_host . '/auth/login?callback=' . $callback_url;

            JumpUtil::go($auth_url);
        }
    }

    //判断是否为客服管理员登陆
    public static function checkCustomAdmin(){
        $group = intval(CookieUtil::get('group'));

        if($group == 1){
            return true;
        }else{
            return false;
        }
    }
}
