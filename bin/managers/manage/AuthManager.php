<?php
/**
 * 身份认证
 * @classname: AuthManager
 * @author 11942518@qq.com | quenteen
 * @date 2016-6-28
 */
class AuthManager extends Manager{

    public function checkAcl(){
        $module = G::$param['route']['m'];
        $controller = G::$param['route']['c'];
        $action = G::$param['route']['a'];

        // file/showImage 无需用户信息
        if ($controller === 'file' && $action === 'showImage') {
            return true;
        }

        // 获取当前登陆用户信息
        $now_user = UserLib::getNowUser();

        if ($now_user) {//已登陆用户
            //获取当前用户的角色：role_type >= 0
            $role_type = 1;
        }else{//未登陆用户
            //除非是allow权限，否则，都返回false
            //role_type的值本身始终大于0
            //所以对于非登录用户，使用-1这个值，保证对于非allow权限的请求，都返回false
            $role_type = -1;
        }
        $flag = $this->checkUserPageAuth($role_type);

        return $flag;
    }

    public function checkUserPageAuth($role_type){
        $module = G::$param['route']['m'];
        $controller = G::$param['route']['c'];
        $action = G::$param['route']['a'];

        $is_ajax = Yii::app()->request->isAjaxRequest;

        $role_type_arr = array('role_type' => $role_type);

        if($this->localAcl->get($module, $controller, $action) === false){//不存在该action
            $jump_url = HttpUtil::getBaseHost() . '/recommend/index?404';
            JumpUtil::go($jump_url);
            return false;
        }else if ($this->localAcl->check($module, $controller, $action, $role_type_arr)) {
            $platform = HttpUtil::getPlatform();
            if($platform['wft']){
                $this->checkPermission();
            }
            return true;
        } else {//处理权限不够的情况
            if($role_type == -1){//未登陆的引导去登陆
                $flag = $this->checkPermission();
                return $flag;
            }else{//已登陆，但权限不够的，引导去登陆
                if(HttpUtil::checkAjaxForHttpReturn() == false){
                    $to_page = 'auth/login';
                    $url = Yii::app()->createUrl($to_page);
                    Yii::app()->request->redirect($url);
                }
            }
        }
    }

    /**
     * 检测用户登陆，微信&app自动注册和登陆，并返回用户登陆信息
     * @return app => post过来的参数信息
     * @return web => 用户的id信息
     * @author 11942518@qq.com | quenteen
     * @date 2016-6-28 下午07:25:09
     */
    public function checkPermission($is_check = true){
        $pass = false;

        $platform = HttpUtil::getPlatform();
        $user_vo = UserLib::getNowUser();

        if($platform['wechat']){
            if($user_vo){
                $pass = true;
                return $user_vo;
            }

            $service = new WechatConfigService();

            $wechat_callback_code = $_GET['code'];
            //微信端，如果用户未登陆，且有微信回调参数，则进行登陆用户初始化操作
            if($wechat_callback_code && !$user_vo){
                $user_access_token_arr = $service->getUserAccessTokenByCallbackCode($wechat_callback_code);

                if(isset($user_access_token_arr['openid'])){
                    $user_access_token = $user_access_token_arr['access_token'];
                    $wechat_user = $service->getUserInfoByUserToken($user_access_token);

                    if(isset($wechat_user['openid'])){
                        //根据微信用户 open_id 和 union_id 反查用户是否注册
                        $user_vo = UserLib::initUserInfoByWechatUser($wechat_user, $user_access_token_arr);

                        if($user_vo){
                            $pass = true;

                            CookieUtil::set('uid', $user_vo['uid']);
                            CookieUtil::set('phone', $user_vo['phone']);
                            CookieUtil::set('token', $user_vo['token']);
                            CookieUtil::set('open_id', $user_vo['wechat_user']['open_id']);
                        }
                    }
                }
            }

            //如果是微信端，则一定要求登陆，否则，跳转至授权页面
            if(!$pass){
                if(HttpUtil::checkAjaxForHttpReturn() == false){
                    $auth_url = $service->getAuthorUrl();
                    JumpUtil::go($auth_url);
                }
            }
        }else if($platform['wft']){
            $data = HttpUtil::processParam();
            if($data){
                $param_arr = json_decode($data, true);

                if ($param_arr) {
                    $uid = intval($param_arr['uid']);
                    $token = $_SERVER['HTTP_TOKEN'];

                    if($uid){
                        $flag = true;

                        if($token){
                            if (self::checkToken($uid, $token) == false) {
                                $flag = false;
                            }
                        }

                        if($flag){
                            $user_vo = UserData::getUserById($uid);
                            if($user_vo){
                                UserLib::setNowUser($user_vo);
                            }
                        }
                    }else{
                        UserLib::setNowUser(null);
                    }
                }
            }

            if($user_vo){
                $pass = true;
            }
        }else{//普通浏览器
            if($user_vo){
                $pass = true;
                return $user_vo;
            }else{
                if(HttpUtil::checkAjaxForHttpReturn() == false){
                    $base_host = HttpUtil::getBaseHost();

                    $callback_url = $base_host . $_SERVER['REQUEST_URI'];
                    $callback_url = urlencode($callback_url);
                    $auth_url = $base_host . '/auth/login?callback=' . $callback_url;

                    JumpUtil::go($auth_url);
                    //JumpUtil::go('http://wan.wanfantian.com/app/index.php');
                }
            }
        }

        if($pass){
            return $user_vo;
        }else{
            return false;
        }
    }

    /**
     * 检测token是否正确
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-6-28 下午04:18:26
     */
    public static function checkToken($uid, $token){
        if(!$uid || !$token) {
            return false;
        }

        $cache_token = CacheUtil::getX('user_token_' . $uid);
        if ($cache_token) {
            if ($token === $cache_token) {
                return true;
            }
        }

        $cmd = Yii::app()->db->createCommand('select token from play_user where uid = :uid');
        $cmd->bindValue(':uid', $uid);
        $user_token = $cmd->queryScalar();

        CacheUtil::setX('user_token_' . $uid, $user_token, 604800);

        if ($user_token === $token) {
            return true;
        } else {
            return false;
        }
    }

    //清除wap cookie
    public static function clearWapCookie(){
        CookieUtil::del('uid');
        CookieUtil::del('token');
        CookieUtil::del('open_id');
        CookieUtil::del('phone');
    }
}