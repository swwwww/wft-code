<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/10/22
 * Time: 11:12
 */
class AlipayService
{

    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    private $partner;  //合作身份者id，以2088开头的16位纯数字
    private $private_key_path    = 'key/rsa_private_key.pem';//商户的私钥（后缀是.pen）文件相对路径
    private $ali_public_key_path = 'key/alipay_public_key.pem'; //支付宝公钥（后缀是.pen）文件相对路径

    //商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
    private $private_key = 'MIICXAIBAAKBgQC/sR6ozOWX6fwyhP/bNV2hpWg7IsxBTqEI2dtOJ8Sm64oQIw3OFVSLMCGyp2G55in3cjt7UtRfXllTRdvKUueK13emEuvBFKn5Bi+8xkUNDY7dE7nbnT4k4EgT4E1RXrtwbyq5mxZ1uii+BRgYpX8JThD+uHMSYnNIxZVeOpC2ZwIDAQABAoGAPrdycoPnMlbJvrWpCE1jfvmhKofuEAfrw/uTNvTn8DzsBO+TGrP73zY2MD93R904Kc9kCqtE9Jbn3sjiakXJnyBYqBJBMjAWkT/fLzxMZtNuSijl+BblbrE2IAPIMkSuEF8OEvc5cr8ft4zaFO+u0dPW1yp7FgWk2UBgGDStsmECQQDn2BRg0kpDliR+2514AGU0AHujaVUt3OoL3RvFfLogopWJIxkBdxR1lM6r1T8EQ6YHLyGXmg1ebmwQ4036PlOpAkEA06oSFC066hfNScny5eyOEC0zVtvR20Saw+6YkYkZLZ325P9ptkupqQdUwNmSqoYJlPTiAfI0jH9S12YNO70DjwJBAOEjAr721qkFLxGFgEfc0moKIgYQrmeoBBtbLrG7Kh+w4ldWnty+Xz7DL2LL5LLmYl7NlOhb76mIvyYzJTDhv2kCQBqbMDaVEOjIISf7WKsKNzlVVTS/4Ps8/m9OmKMCpsWTK6vRZ0pg7Gyw3Th4oPUKcD3nIlm4Rl66yoEE9PjjY4UCQFEk1CdQpOTf1Gk7OWUPYLNhX9Ffy9JtpuscAI8A1CgN5aP5L84iuZq9YmTMoIfx6ZVSydH4wrjrdlKFA9YSUNk=';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
    private $ali_public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

    //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    private $service = 'alipay.wap.create.direct.pay.by.user';  //默认
    private $seller_id; //收款账号
    private $charset = 'utf-8';  //字符编码格式 目前支持 gbk 或 utf-8
    //private $notify_url = 'http://wft.deyi.com/web/notify/ali';  //回调地址
    private $notify_url;  //回调地址
    private $return_url;
    private $sign_type          = 'RSA'; //签名方式 不需修改
    private $cacert             = 'cacert.pem';  //ca证书路径地址，用于curl中ssl校验    //请保证cacert.pem文件在当前文件夹目录中
    private $transport          = 'http'; //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    private $alipay_gateway_new = "https://mapi.alipay.com/gateway.do?";

    public function __construct()
    {
        $this->private_key_path    = __DIR__ . '/' . $this->private_key_path;
        $this->ali_public_key_path = __DIR__ . '/' . $this->ali_public_key_path;

        $this->partner    = "2088121589013833";
        $this->seller_id  = '2088121589013833';

        $notify_url = YII_DEBUG ? "http://test.wanfantian.com/web/notify/ali" : "https://api.wanfantian.com/web/notify/ali";
        if (YII_PAY_DEV) {
            $notify_url = YII_DEBUG ? "http://test.wanfantian.com/web/notify/ali" : "http://test.wanfantian.com/web/notify/ali";
        }
        $this->notify_url = $notify_url;
        $this->MD5_key    = '';

    }

    public function getAlipayConfig()
    {

        $alipay_config['partner']             = $this->partner;
        $alipay_config['cacert']              = $this->cacert;
        $alipay_config['seller_id']           = $this->seller_id;
        $alipay_config['sign_type']           = $this->sign_type;
        $alipay_config['transport']           = $this->transport;
        $alipay_config['private_key']         = $this->private_key;
        $alipay_config['input_charset']       = $this->charset;
        $alipay_config['input_charset']       = $this->charset;
        $alipay_config['ali_public_key']      = $this->ali_public_key;
        $alipay_config['private_key_path']    = $this->private_key_path;
        $alipay_config['ali_public_key_path'] = $this->ali_public_key_path;
        return $alipay_config;
    }


    /**
     * 生成支付宝支预请求信息
     *
     * @param $order_sn         服务器生成的唯一订单号
     * @param $subject          订单标题
     * @param $total_fee        付款金额
     * @param $body             商品详情
     * @param $id               商品id
     * @param $count            购买数量
     * @param $name             购买者姓名
     * @param $phone            购买者手机号
     *
     * @return array
     */
    public function alipayParam($order_sn, $subject, $total_fee, $body, $return_url, $flag=0)
    {
        $order_sn = StringUtil::getDebugOrderSn($order_sn, 'alipay');
        $ali_arr = array(
            'service'        => $this->service,
            'partner'        => $this->partner,
            '_input_charset' => $this->charset,
            'notify_url'     => $this->notify_url,//回调地址
            'return_url'     => $return_url,//回调地址
            'out_trade_no'   => $order_sn,//商户网站唯一订单号
            'subject'        => $subject,//商品名称
            'payment_type'   => 1,//支付类型
            'seller_id'      => $this->seller_id,//支付宝账号
            'total_fee'      => $total_fee,//总金额
            'body'           => $body,//商品详情
            'it_b_pay'       => '30m' //关闭订单时间
        );

        if ($flag == 1) {
            $ali = $this->buildRequestForm($ali_arr, 'get', '确认');

        } else {
            $ali = $this->buildRequestUrl($ali_arr);

        }

        return $ali;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     *
     * @param $para_temp   请求参数数组
     * @param $method      提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     *
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $button_name)
    {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=" . trim(strtolower($this->charset)) . "' method='" . $method . "' target='__blank'>";
//        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=" . trim(strtolower($this->charset)) . "' method='" . $method . ">";
        while (list ($key, $val) = each($para)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit'  value='" . $button_name . "' style='display:none;'></form>";

        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 建立请求，以URL STRING构造（默认）
     *
     * @param $para_temp   请求参数数组
     * @param $method      提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     *
     * @return 提交表单HTML文本
     */
    function buildRequestUrl($para_temp)
    {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        $s_url = $this->alipay_gateway_new;
        while (list ($key, $val) = each($para)) {
            $s_url .= "&" . $key . "=" . urlencode($val);
        }
        return $s_url;
    }

    /**
     * 排序
     *
     * @param $para
     *
     * @return mixed
     */
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 生成要请求给支付宝的参数数组
     *
     * @param $para_temp 请求前的参数数组
     *
     * @return 要请求的参数数组
     */
    function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = self::paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = self::argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign']      = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->sign_type));

        return $para_sort;
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param $para 签名参数组
     *              return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 生成签名结果
     *
     * @param $para_sort 已排序要签名的数组
     *                   return 签名结果字符串
     */
    function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = self::createLinkstring($para_sort);

        $mysign = "";
        switch (strtoupper(trim($this->sign_type))) {
            case "RSA" :
                $mysign = self::rsaSign($prestr, $this->private_key);
                break;
            default :
                $mysign = "";
        }

        return $mysign;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param $para 需要拼接的数组
     *              return 拼接完成以后的字符串
     */
    function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * RSA签名
     *
     * @param $data        待签名数据
     * @param $private_key 商户私钥字符串
     *                     return 签名结果
     */
    function rsaSign($data, $private_key)
    {
        //以下为了初始化私钥，保证在您填写私钥时不管是带格式还是不带格式都可以通过验证。
        $private_key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("-----END RSA PRIVATE KEY-----", "", $private_key);
        $private_key = str_replace("\n", "", $private_key);

        $private_key = "-----BEGIN RSA PRIVATE KEY-----" . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . "-----END RSA PRIVATE KEY-----";

        $res = openssl_get_privatekey($private_key);

        if ($res) {
            openssl_sign($data, $sign, $res);
        } else {
            echo "您的私钥格式不正确!" . "<br/>" . "The format of your private_key is incorrect!";
            exit();
        }
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
}
