<?php
class SiteController extends Controller{

    public function actionSorry(){
        $error = Yii::app()->errorHandler->error;
        if($error['code'] == 500){
            if(Yii::app()->request->isAjaxRequest){
                $result = array(
                    'status' => 0,
                    'error_code' => $error['code'],
                    'msg' => $error["message"],
                );

                HttpUtil::out($result);
            }else{
                if(YII_DEBUG){
                    $error_wrap = array(
                        'data' => $error,
                        'get' => $_GET,
                        'post' => $_POST,
                        'cookie' => $_COOKIE,
                        'server' => $_SERVER,
                    );
                }else{
                    $error_wrap = array(
                        'data' => $error,
                        'get' => $_GET,
                        'post' => $_POST,
                    );
                }

                $trace_arr = array();
                $trace_arr[] = TimeUtil::getNowDateTime() . '---' . $error['code'] . '#!#' . $error['type'];
                $trace_arr[] = $error['file'] . '#!#' . $error['line'];
                $trace_arr[] = $error['message'];
                $trace_arr[] = $error['trace'];
                $trace_arr[] = $_SERVER['REQUEST_URI'];
                $trace_arr[] = $_SERVER['HTTP_REFERER'];

                $trace = implode(PHP_EOL, $trace_arr);

                //$error_string = preg_replace('/\n/', '', json_encode($error_wrap));
                //$error_string .= PHP_EOL;


                /*
                 $error->url = $_SERVER['REQUEST_URI'];
                 $error->referrer = $_SERVER['HTTP_REFERER'];
                 $error->useragent = $_SERVER['HTTP_USER_AGENT'];
                 $error->error_code = $error['code'];
                 $error->type = $error['type'];
                 $error->detail = $error['message'];
                 $error->file_path = $error['file'];
                 $error->file_line_num = $error['line'];
                 $error->trace = $error['trace'];
                 $error->source = json_encode($error['source']);
                 $error->module = G::$conf['ROUTE']['m'];
                 $error->controller = G::$conf['ROUTE']['c'];
                 $error->action = G::$conf['ROUTE']['a'];
                 $error->get_variable = json_encode($_GET);
                 $error->post_variable = json_encode($_POST);
                 $error->server_variable = json_encode($_SERVER);
                 $error->cookie_variable = json_encode($_COOKIE);
                 */

                LogUtil::debug($trace);
                LogUtil::error($trace);

                $log_file = fopen('/mnt/log/playsky/ps_error.log', 'a+');
                fwrite($log_file, $trace . PHP_EOL);
                fclose($log_file);
            }
        }

        $this->tpl->display('site/index.html');
    }

    /**
     * 提供验证码服务
     * @Description:
     * @Title: actionVcode
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2015-1-7 上午12:20:57
     */
    public function actionVcode(){
        $code = VcodeUtil::get();

        $_SESSION['verify_code'] = strtolower($code);

        VcodeUtil::draw($code);
    }

    public function actionWechatLogin(){
        $callback = $_GET['callback'];

        if($callback){
            JumpUtil::go($callback);
        }else{
            $url = HttpUtil::getBaseHost();
            $url .= '/recommend/index';
            JumpUtil::go($url);
        }
    }

    public  function  actionDownload(){
//        $android_url_standby = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.deyi.wanfantian&g_f=991653';       //安卓下载 备用地址
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if(strpos($agent,'iPhone')){
            $ios_url = 'https://itunes.apple.com/cn/app/de-yi-sheng-huo-lun-tan/id950652997?mt=8';
            JumpUtil::go($ios_url);

        }
        elseif (strpos($agent,'Android')){
            $android_url = 'http://wan.wanfantian.com/download/wft.apk';
            JumpUtil::go($android_url);
        }
    }
}