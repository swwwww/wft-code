<?php
/**
 * 日志 帮助类
 * @Description:
 * @ClassName: LogUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-21 上午10:31:02
 */
class LogUtil extends Manager{

    public static function trace($source, $cateroy = 'playsky.trace'){
        Yii::log($source, CLogger::LEVEL_TRACE, $cateroy);
    }

    public static function info($source, $cateroy = 'playsky.info'){
        Yii::log($source, CLogger::LEVEL_INFO, $cateroy);
    }

    public static function error($source, $cateroy = 'playsky.error'){
        Yii::log($source, CLogger::LEVEL_ERROR, $cateroy);
    }

    public static function debug($info){
        //if(YII_DEBUG){
        if(true){
            echo "<pre>";
            print_r($info);
            echo "</pre>";
        }
    }

    public static function log($info, $mark = 'add', $type = 'screen'){
        $mark_str = '---';
        if($mark == 'add'){
            $mark_str = '++++++';
        }
        if($type == 'screen'){
            self::logScreen($info, $mark_str);
        }else if($type == 'file'){
            self::logFile($info, $mark_str);
        }else if($type == 'db'){
            self::logDb($info);
        }
    }

    public static function logScreen($info, $mark){
        echo date('Y-m-d H:i:s') . "\t{$mark}" . $info . "\n";
    }

    public static function logFile($source){
        self::trace($source);
    }

    public static function logDb($info, $class_name, $fun_name, $type = 1){
        $log = new LogVo();
        $log->class_name = $class_name;
        $log->fun_name = $fun_name;
        $log->detail = $info;
        $log->type = $type;
        $log->created = TimeUtil::getNowDateTime();

        $log->save();
    }

    public static function logAccess() {
        if(YII_DEBUG){
            return true;
        }
        // in case of anything unexpected happens
        try{
            /*
             if (preg_match('/inf-ssl-duty-scan/', $_SERVER['HTTP_USER_AGENT'])) {
             return; // ignore inf-ssl
             }*/

            $result = array();

            $user_id = $_SESSION['user']['id'];
            $user_name = $_SESSION['user']['user_name'];
            $module_name = G::$param['route']['m'];
            $dir_name = G::$param['route']['d'];
            $controller_name = G::$param['route']['c'];
            $action_name = G::$param['route']['a'];
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]);
                $real_ip = $ip[0];
            } else {
                $real_ip = $_SERVER["REMOTE_ADDR"];
            }

            if ($_SERVER['HTTP_X_HOST'] == "wanfantian.com") {
                $http_host = $_SERVER['HTTP_X_HOST'];
            } else {
                $http_host = $_SERVER['HTTP_HOST'];
            }

            $uri = $_SERVER['REQUEST_URI'];
            $status = $_SERVER['REDIRECT_STATUS'];
            $method = $_SERVER['REQUEST_METHOD'];
            $time = date("Y-m-d H:i:s");
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $referer = $_SERVER['HTTP_REFERER'];
            $cookie = $_SERVER['HTTP_COOKIE'];
            $phpsess_id = $_COOKIE['wanfantian-uuid'];
            $get = @json_encode($_GET);
            $post = @json_encode($_POST);

            $timeLog = Yii::getLogger();
            $process_time = intval($timeLog->getExecutionTime() * 1000);//毫秒级

            $result[] = $time;
            $result[] = $status;
            $result[] = $module_name;
            $result[] = $controller_name;
            $result[] = $action_name;
            $result[] = $http_host . $uri;
            $result[] = $real_ip;
            $result[] = $process_time;
            $result[] = $http_host;
            $result[] = $referer;
            $result[] = $user_id;
            $result[] = $user_name;
            $result[] = $phpsess_id;
            $result[] = $method;
            $result[] = $get;
            $result[] = $post;
            $result[] = $user_agent;
            $result[] = $cookie;
            //$result[] = $dir_name;

            $log_string = implode('@*!*!*@', $result);

            //$logFile = fopen('/work/log/play_access_log/' . date('Ymd') . '_ps.log', 'a+');
            $logFile = fopen('/mnt/log/playsky/' . date('Ymd') . '_ps.log', 'a+');
            fwrite($logFile, $log_string . PHP_EOL);
            fclose($logFile);
        }catch(Exception $e){
            // we do nothing
        }
    }
}