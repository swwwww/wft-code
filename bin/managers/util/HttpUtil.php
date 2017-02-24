<?php

/**
 * http头 设置帮助类
 *
 * @Description:
 * @ClassName  : HttpUtil
 * @author     Quenteen || qintao ; hi：qintao870314 ;
 * @date       2015-2-8 下午07:38:19
 */
class HttpUtil extends Manager
{

    public static function out($result)
    {
        echo json_encode($result);
        Yii::app()->end();
    }

    /**
     * 强制刷新缓存，防止运营商劫持
     *
     * @Description:
     * @Title      : initHttpCache
     * @author     Quenteen || qintao ; hi：qintao870314 ;
     * @date       2014-11-19 下午11:10:34
     */
    public static function initHttpCache()
    {
        if (G::$param['route']['c'] !== 'file') {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pramga: no-cache');
        }
    }

    //获取平台类型：微信 | 玩翻天
    public static function getPlatform()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        $wechat = strstr($agent, 'micromessenger') ? true : false;
        $wft    = strstr($agent, 'wft') ? true : false;
        $str    = strtolower($_SERVER['HTTP_USER_AGENT']);
        preg_match_all("#/(\w+)/client/(\w+.+)#", $str, $phone);

        $result = array(
            'wechat'     => $wechat,
            'wft'        => $wft,
        $phone[1][0] => $phone[2][0],
            'version' => $phone[2][0],
        );

        return $result;
    }

    //额外处理app的p参数，进行相关字符串的替换
    public static function processParam()
    {
        $param = $_REQUEST['p'];
        if($param){
            $param = preg_replace(array('/-/', '/_/'), array('+', '/'), $param);

            $data = SecretUtil::decrypt($param);
            return $data;
        }else{
            return false;
        }
    }

    public static function getBaseHost()
    {
        $base_host = Yii::app()->request->hostInfo;

        if(!YII_DEBUG && !YII_PAY_DEV && !strstr($base_host, 'https://')){
            $base_host = str_replace('http', 'https', $base_host);
        }

        return $base_host;
    }

    public static function getHttpResult()
    {
        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );

        return $result;
    }

    public static function setP($source, $url, $method = 'post', $ver = 10, $city = '', $isFile=false)
    {

        $source = $source == null ? array() : $source;
        // 获取当前用户组装p参数必要数据
        $user_vo = UserLib::getNowUser();

        if ($user_vo) {
            $source['uid']   = intval($user_vo['uid']);
            $source['token'] = $user_vo['token'];
        }

        //固定时间参数
        $source['timestamp'] = TimeUtil::getNowDateTime();

        $source_result = json_encode($source);
        $source_result = SecretUtil::encrypt($source_result);
        $data          = array('p' => $source_result);

        // 解决某些接口不经过p参数的问题
        if ($isFile){
            $data['file'] = $source['file'];
        }

        // 判断前置host   172.16.2.10          119.97.180.103
        $host = YII_DEBUG ? 'http://10.0.32.10' : 'https://api.wanfantian.com';
        if (YII_PAY_DEV) {
            $host = YII_DEBUG ? 'http://test.wanfantian.com:88' : 'http://test.wanfantian.com:88';
        }

        $target_url = $host . $url;
        //city没有主动传入，则从cookie中获取
        $city = StringUtil::getCustomCity($city);
        $city = StringUtil::getCityForCn($city);
        $header = array(
            'ver'  => $ver,
            'city' => $city,
        );
        // 发送请求
        $proxy    = new ProxyUtil($method, $target_url, $data, $header);

        $response = $proxy->run();
        $result   = json_decode($response, true);
        if (isset($result['response_params'])) {
            // 返回请求数据
            $temp_result = $result['response_params'];
            $temp_msg    = "";
            if (isset($temp_result['msg'])) {
                $temp_msg = $temp_result['msg'];
            } elseif (isset($temp_result['message'])) {
                $temp_msg = $temp_result['message'];
            }
            $res = array(
                'status' => 1,
                'msg'    => $temp_msg,
                'data'   => $temp_result,
            );
            return $res;
        } else if ($result['error_code'] == 0 || $result['error_code'] == 1001) {
            $res = array(
                'status' => 0,
                'msg'    => $result['error_msg'],
                'data'   => $result,
            );

            LogUtil::trace("[HttpUtil][post error][{$url}]: " . json_encode($source) . "\t" . $result['error_msg']);
            return $res;
        } else {
            LogUtil::error("[HttpUtil](setP) 入参(json_encode):" . json_encode($source) . "请求地址:" . $url . "返回数据为空");

            return false;
        }
    }

    public static function proxyBusiness($source, $url, $method = 'post', $ver = 10, $city = '', $isFile=false)
    {
        $source = $source == null ? array() : $source;
        // 获取当前用户组装p参数必要数据
        $user_vo = UserLib::getBusinessNowUser();

        if ($user_vo) {
            $source['organizer_id']   = intval($user_vo['organizer_id']);
            $source['rc_auth'] = $user_vo['rc_auth'];
        }
        //固定时间参数
        $source['timestamp'] = TimeUtil::getNowDateTime();

        $source_result = json_encode($source);
        $source_result = SecretUtil::encrypt($source_result);
        $data          = array('p' => $source_result);

        // 解决某些接口不经过p参数的问题
        if ($isFile){
            $data['file'] = $source['file'];
        }

        // 判断前置host   172.16.2.10          119.97.180.103
        $host = YII_DEBUG ? 'http://10.0.32.10' : 'https://api.wanfantian.com';
        if (YII_PAY_DEV) {
            $host = YII_DEBUG ? 'http://test.wanfantian.com:88' : 'http://test.wanfantian.com:88';
        }

        $target_url = $host . $url;
        //city没有主动传入，则从cookie中获取
        $city = StringUtil::getCustomCity($city);
        $city = StringUtil::getCityForCn($city);
        $header = array(
            'ver'  => $ver,
            'city' => $city,
        );
        // 发送请求
        $proxy    = new ProxyUtil($method, $target_url, $data, $header);
        $response = $proxy->run();
        $result   = json_decode($response, true);

        if (isset($result['response_params'])) {
            // 返回请求数据
            $temp_result = $result['response_params'];
            $temp_msg    = "";
            if (isset($temp_result['msg'])) {
                $temp_msg = $temp_result['msg'];
            } elseif (isset($temp_result['message'])) {
                $temp_msg = $temp_result['message'];
            }
            $res = array(
                'status' => $temp_result['status'], /*1*/
                'msg'    => $temp_msg,
                'data'   => $temp_result,
            );
            return $res;
        } else if ($result['error_code'] == 0 || $result['error_code'] == 1001) {
            $res = array(
                'status' => 0,
                'msg'    => $result['error_msg'],
                'data'   => $result,
            );

            LogUtil::trace("[HttpUtil][post error][{$url}]: " . json_encode($source) . "\t" . $result['error_msg']);
            return $res;
        } else {
            LogUtil::error("[HttpUtil](setP) 入参(json_encode):" . json_encode($source) . "请求地址:" . $url . "返回数据为空");

            return false;
        }
    }

    public static function checkAjaxForHttpReturn(){
        $is_ajax = Yii::app()->request->isAjaxRequest;

        if($is_ajax){
            $result = HttpUtil::getHttpResult();
            $result['code'] = '401';
            $result['msg'] = '亲，登陆后再操作';
            HttpUtil::out($result);
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param     $xml
     * @param     $url
     * @param int $second
     *
     * @return string
     */
    public static function postXmlFileGetContents($xml, $url, $second = 60)
    {
        $opts    = array('http' =>
        array(
                                 'method'  => 'POST',
                                 'header'  => "Content-type: application/x-www-form-urlencoded",
                                 'content' => $xml,
                                 'timeout' => $second
        )
        );
        $context = stream_context_create($opts);
        $res     = file_get_contents($url, false, $context);
        return $res;
    }
}
