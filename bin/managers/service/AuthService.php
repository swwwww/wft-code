<?php
/**
 * 登陆服务
 * @classname: AuthService
 * @author 11942518@qq.com | quenteen
 * @date 2016-10-19
 */
class AuthService extends Manager
{
    public function __construct(){

    }

    /**
     * 手机号登陆和注册
     * @return
     * status = 0:
     *     msg: 错误信息
     * status = 1:
     * data = array(
     *     'phone' => $phone,
     *     'wechat_user_id' => $wechat_user_vo['id'],
     *     'old_user_id' => $old_user_id,
     *     'new_user_id' => $phone_user_id,
     * );
     * @author 11942518@qq.com | quenteen
     * @date 2016-10-19 下午04:37:57
     */
    public function submitPhoneLogin($data, $event_id = 0){
        $result = HttpUtil::getHttpResult();
    
        $phone = trim($data['phone']);
        $code = trim($data['code']);

        $user_vo = UserLib::getNowUser();
        //手机已登录的用户，直接退出
        if($user_vo && $user_vo['phone']){
            $result['msg'] = '您的手机号已经登陆，无需重复登陆';
            return $result;
        }

        $old_user_id = $user_vo['uid'];
    
        if(!$user_vo['phone']){

            $sms_service = new SmsService();
            if($sms_service->checkCode($phone, $code)){//验证码核对

                $phone_user_vo = UserLib::initPlayUserByPhone($phone);//初始化用户手机

                if($phone_user_vo){

                    $result['status'] = 1;
                    $result['msg'] = '登陆成功';
                    $phone_user_id = $phone_user_vo['uid'];

                    if($user_vo){//表明是微信登陆的用户，且没有手机号

                        $app_id = Yii::app()->params['wechat']['appid'];
                        //获取用户微信信息，准备更新
                        $wechat_user_vo = WechatUserVo::model()->find("uid = :uid and appid = :app_id and login_type = 'weixin_wap'", array(
                            ':uid' => $old_user_id,
                            ':app_id' => $app_id,
                        ));


                        if($wechat_user_vo){

                            $wechat_user_vo->uid = $phone_user_id;//更新微信用户的uid
                            $wechat_user_vo->save();

                            //session更新为最终的正确用户
                            $user_vo = UserData::getUserById($phone_user_id);
                            UserLib::setNowUser($user_vo);

                            $change_data = array(
                                'phone' => $phone,
                                'wechat_user_id' => $wechat_user_vo['id'],
                                'old_user_id' => $old_user_id,
                                'new_user_id' => $phone_user_id,
                            );
                            //记录用户变更日志
                            $change_flag = UserLib::processUserChangeLog($change_data, $event_id);

                            $result['data'] = $change_data;
                        }

                    }else{//普通浏览器注册/登陆用户

                        //session更新为最终的正确用户
                        $user_vo = UserData::getUserById($phone_user_id);
                        UserLib::setNowUser($user_vo);

                        $rst_data = array(
                            'new_user_id' => $phone_user_id,
                        );

                        $result['data'] = $rst_data;
                    }
                }else{
                    $result['msg'] = '网络开小差了，请刷新后重试';
                }
            }else{
                $result['msg'] = '验证码错误';
            }
        }

        return $result;
    }
}
