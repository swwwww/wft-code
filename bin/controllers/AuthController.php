<?php
/**
 * 登陆
 * @classname: AuthController
 * @author 11942518@qq.com | quenteen
 * @date 2016-10-19
 */
class AuthController extends Controller{

    public function actionLogin(){
        $user_vo = UserLib::getNowUser();

        $data = $_GET;
        $callback_url = $data['callback'];

        $platform = HttpUtil::getPlatform();

        //手机已登录的用户，直接退出
        if($user_vo && $user_vo['phone']){
            $this->redirect($this->createUrl('recommend/index'));
        }else if($user_vo == null && $platform['wechat']){
            $base_host = HttpUtil::getBaseHost();
            $callback_url = $base_host . $_SERVER['REQUEST_URI'];

            $url = $this->createUrl('site/wechatLogin');
            $url .= '?callback=' . $callback_url;
            $this->redirect($url);
        }else{
            $this->tpl->assign('callback_url', $callback_url);
            $this->tpl->display('auth/m/login.html');
        }
    }

    public function actionLogout(){
        Yii::app()->session->destroy();

        //$callback_url = $_SERVER['HTTP_REFERER'];
        $base_host = HttpUtil::getBaseHost();
        $callback_url = $base_host . '/recommend/index';

        JumpUtil::go($callback_url);
    }

    public function actionSubmitPhoneLogin(){
        $data = $_POST;
        $result = HttpUtil::getHttpResult();

        $service = new AuthService();
        $submit_result = $service->submitPhoneLogin($data);

        $result = $submit_result;
        if($submit_result['status'] == 1){
            $is_new_user = $submit_result['is_new_user'];

            $submit_data = $submit_result['data'];
            $user_id = $submit_data['new_user_id'];
            $result['data'] = array(
                'user_id' => $user_id,
                'is_new_user' => $is_new_user,
            );
        }

        HttpUtil::out($result);
    }
}