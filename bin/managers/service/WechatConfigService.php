<?php

/**
 * 微信服务端 接口
 * 获取微信端的配置文件使用
 *
 * @classname: WechatConfigService
 * @author   11942518@qq.com | quenteen
 * @date     2016-7-1
 */
class WechatConfigService extends Manager
{
    public $app_id;
    public $secret;
    public $token;
    public $access_token;
    public $wx_config;

    public function __construct($wechat = 'wechat')
    {
        $config = Yii::app()->params[$wechat];

        $this->app_id = $config['appid'];
        $this->secret = $config['secret'];
        $this->token = $config['token'];
        $this->wx_config = $config;
    }

    // 获取授权页面url
    public function getAuthorUrl($callbackUrl = false, $scope = 'snsapi_userinfo', $state = '123')
    {
        if (!$callbackUrl) {
            $action_url = $_SERVER['REQUEST_URI'];
            $callbackUrl = 'http://wan.wanfantian.com/wan-to-play' . $action_url;
        }
        $callbackUrl = urlencode($callbackUrl);

        //LogUtil::info('[WechatConfigService][getAuthorUrl]: ' . $callbackUrl);

        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$callbackUrl}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    //根据微信授权登陆页面 的 返回code，获取用户access_token
    public function getUserAccessTokenByCallbackCode($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->secret}&code={$code}&grant_type=authorization_code";
        $proxy = new ProxyUtil('get', $url);
        $access_token = $proxy->run();

        return json_decode($access_token, true);
    }

    //根据用户token获取用户信息
    public function getUserInfoByUserToken($user_access_token)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$user_access_token}&openid={$this->app_id}&lang=zh_CN";
        $proxy = new ProxyUtil('get', $url);
        $user_info = $proxy->run();

        return json_decode($user_info, true);
    }

    // 获取access_token
    public function getAccessToken($app_id = '', $secret = '')
    {
        $app_id = $app_id ? $app_id : $this->app_id;
        $secret = $secret ? $secret : $this->secret;

        $aToken = CacheUtil::getX("accessToken{$app_id}");
        if ($aToken) {
            LogUtil::info('[WechatConfigService](getAccessToken) wechat get access token from cache: ' . $aToken);
            return $aToken;
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$app_id}&secret={$secret}";

            $proxy = new ProxyUtil('get', $url);
            $res = $proxy->run();

            $token_data = json_decode($res, true);
            if ($token_data['access_token']) {
                CacheUtil::setX("accessToken{$app_id}", $token_data->access_token, $token_data['expires_in'] - 5);
                return $token_data['access_token'];
            } else {
                return false;
            }
        }
    }

    // 生成JSAPI 签名，返回所需的所有签名数据
    public function getSignature()
    {
        $base_host = HttpUtil::getBaseHost();
        $data = array(
            'noncestr'     => StringUtil::getNonceStr(),  //随机字符串
            'jsapi_ticket' => $this->getJsApiTicket(),
            'timestamp'    => time(),
            'url'          => $base_host . $_SERVER['REQUEST_URI'],
        );

        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= "$k=$v&";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $signature = sha1($str);

        $data['signature'] = $signature;
        $data['app_id'] = $this->app_id;

        return $data;
    }

    // 获取 ticket
    public function getJsApiTicket()
    {
        $ticket = CacheUtil::getX("weixin_ticket{$this->app_id}");
        if (!$ticket) {
            $token = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$token}&type=jsapi";
            $proxy = new ProxyUtil('get', $url);
            $res = $proxy->run();
            $ticket_arr = json_decode($res, true);
            $ticket = $ticket_arr['ticket'];
            CacheUtil::setX("weixin_ticket{$this->app_id}", $ticket, 7000);

            return $ticket;
        } else {

            return $ticket;
        }
    }

    // 获取 PaySignature
    public function getPaySignature($data)
    {
        //LogUtil::info('[WechatConfigService](getPaySignature)[start] data:' . json_encode($data));

        ksort($data);
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $string = trim($buff, "&");
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->wx_config['PartnerKey'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    // 验证
    public function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStrSha1 = sha1($tmpStr);
        if ($tmpStrSha1 == $signature) {

            return true;
        } else {

            LogUtil::error('验证失败 RAW:' . $tmpStr . ' sha1:' . $tmpStrSha1);

            return false;
        }
    }



    // 获取微信请求参数
    public function getRequestObject()
    {
        $requestObject = null;
        $requestData = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : null;
        if (!empty($requestData)) {
            $requestObject = simplexml_load_string($requestData, 'SimpleXMLElement', LIBXML_NOCDATA);
        }

        return $requestObject;
    }
}