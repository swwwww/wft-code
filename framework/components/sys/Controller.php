<?php
/**
 * 通用controller父类
 * @Description:
 * @ClassName: Controller
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-19 下午11:01:40
 */
class Controller extends CController{
    //格式化 APP 传递的参数，全局调用
    public $param = array();

    //定义魔术方法 - 可利用单例模式 获取 manager类：使用方式： $this->oper->method()
    public function __get($name){
        $factory = Factory::getInstance($this);

        $this->$name = $factory->getClass($name);

        return $this->$name;
    }

    public function __set($name, $value){
        $this->$name = $value;
    }

    public function beforeAction($action){
        HttpUtil::initHttpCache();
        $this->initController($this->getRoute());

        $flag = $this->initUserComponent();
        if($flag == false){
            if(HttpUtil::checkAjaxForHttpReturn() == false){
                $target = HttpUtil::getBaseHost() . '/auth/login';
                $this->redirect($target);
            }
        }

        return $flag;
    }

    public function initUserComponent(){
        /**
         * Info: When you access session data through the session component,
         * a session will be automastically opened if it has not been done so before.
         * This is different from accessing session data through $_SESSION,
         * which requires an explicit call of session_start().
         * see more:
         * http://www.yiiframework.com/doc-2.0/guide-runtime-sessions-cookies.html
         * directly use $_SESSION (make sure Yii::$app->session->open() has been called)
         */

        //线下可以模拟用户登陆，通过debug_uid参数，可以将该用户id的信息存入session
        //if(YII_DEBUG){
        if(YII_PAY_DEV){
            $user_id = intval($_REQUEST['debug_uid']);

            if($user_id > 0){
                $user_vo = UserData::getUserById($user_id);
                if($user_vo){
                    UserLib::setNowUser($user_vo);
                }
            }
        }

        //线上才开起每个action的权限验证
        $flag = true;
        $flag = $this->auth->checkAcl();
        if(YII_DEBUG){
            CookieUtil::set('YII_DEBUG', 1);
        }else{
            CookieUtil::del('YII_DEBUG');
        }

        //获取平台和APP参数的信息
        $platform = HttpUtil::getPlatform();
        $this->tpl->assign('platform', $platform);

        $custom_city = StringUtil::getCustomCity();
        if($custom_city == 'wuhan'){
            $custom_city_cn = '武汉';
        }else if ($custom_city == 'nanjing'){
            $custom_city_cn = '南京';
        }
        $wft_global_set = array(
            'custom_city' => $custom_city,
            'custom_city_cn' => $custom_city_cn
        );

        $this->tpl->assign('wft_global_set', $wft_global_set);

        if($platform['wft']){
            $data = HttpUtil::processParam();
            if($data){
                $this->param = json_decode($data, true);
                $this->tpl->assign('wft_param', $_REQUEST['p']);
            }
        }

        return $flag;
    }

    public function afterAction($action){
    }

    public function initController($route){
        if(get_magic_quotes_gpc()){
            $this->filter($_GET);
            $this->filter($_POST);
            $this->filter($_COOKIE);
            $this->filter($_REQUEST);
        }
    }

    private function filter($input){
        if(is_array($input)){
            while(list($k, $v) = each($input)){
                if(is_array($input[$k])){
                    while(list($k2, $v2) = each($input[$k])){
                        $input[$k][$k2] = stripslashes($v2);
                    }
                    @reset($input[$k]);
                }else{
                    $input[$k] = stripslashes($v);
                }
            }
            @reset($input);
        }
    }
}