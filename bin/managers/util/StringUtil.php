<?php

/**
 * 字符串帮助类
 *
 * @Description:
 * @ClassName  : StringUtil
 * @author     Quenteen || qintao ; hi：qintao870314 ;
 * @date       2015-2-1 下午10:04:07
 */
class StringUtil extends Manager
{

    /**
     * 获取10位邀请码
     *
     * @param
     *
     * @return
     * @author 11942518@qq.com | quenteen
     * @date   2015-9-15 下午06:34:59
     */
    public static function genInviteCode()
    {
        $code = substr(md5(time() * rand(-10086, 10086)), 0, 10);

        $code = strtoupper($code);

        return $code;
    }

    //获取验证码
    public static function getFirstLetter($source)
    {
        //gb2312 拼音排序
        $letter_arr = array(
        array(45217, 45252), //A
        array(45253, 45760), //B
        array(45761, 46317), //C
        array(46318, 46825), //D
        array(46826, 47009), //E
        array(47010, 47296), //F
        array(47297, 47613), //G
        array(47614, 48118), //H
        array(0, 0),         //I
        array(48119, 49061), //J
        array(49062, 49323), //K
        array(49324, 49895), //L
        array(49896, 50370), //M
        array(50371, 50613), //N
        array(50614, 50621), //O
        array(50622, 50905), //P
        array(50906, 51386), //Q
        array(51387, 51445), //R
        array(51446, 52217), //S
        array(52218, 52697), //T
        array(0, 0),         //U
        array(0, 0),         //V
        array(52698, 52979), //W
        array(52980, 53688), //X
        array(53689, 54480), //Y
        array(54481, 55289), //Z
        );

        $source = iconv('UTF-8', 'gb2312', $source);

        $i = 0;
        while ($i < strlen($source)) {
            $tmp = bin2hex(substr($source, $i, 1));
            if ($tmp >= 'B0') { //汉字的开始
                $t = self::getLetter(hexdec(bin2hex(substr($source, $i, 2))), $letter_arr);
                $t = $t != -1 ? $t : 65;
                $i += 2;
                break;
            } else {
                $t = 65;
                $i++;
            }
        }

        $result = chr($t);
        return $result;
    }

    /**
     * 将字符串的md5值转成数字
     *
     * @param $source
     *
     * @return int
     */
    public static function formatStringToNum($source)
    {
        $source = md5($source);

        $num_arr = array();

        //拆分md5的值，4个为一组，共分为8组
        for ($i = 0; $i < 8; $i++) {
            $item = substr($source, $i * 4, 4);
            $target = hexdec($item);

            $num_arr[] = $target;
        }

        //两两相乘，并除以10,
        $result = 0;
        for ($i = 0; $i < 4; $i++) {
            $target = intval($num_arr[$i * 2] * $num_arr[$i * 2 + 1] / ($i * 10 + 12));
            $target = $target == 0 ? 1 : $target;

            //将4组的结果加在一起，得到最后的数字
            $result += $target;
        }

        return $result;
    }

    public static function getLetter($num, $letter_arr)
    {
        $char_index = 65;

        foreach ($letter_arr as $k => $v) {
            if ($num >= $v[0] && $num <= $v[1]) {
                $char_index += $k;
                return $char_index;
            }
        }

        return -1;
    }

    public static function getSubText($source, $len = 30)
    {
        $result = '';
        if (strlen($source) > $len) {
            $result = mb_strcut($source, 0, $len, 'utf-8');
        } else {
            $result = $source;
        }

        return $result;
    }

    public static function getMobile()
    {
        //h5 for mobile
        $h5 = $_SESSION['h5'];
        $mobile = $h5 ? 'm/' : '';

        return $mobile;
    }

    /**
     * @param $phone
     * 手机号合法性验证
     *
     * @return bool
     * author: MEX | mixmore@yeah.net
     */
    public static function checkPhone($phone)
    {
        if (preg_match("/^1[34578]\d{9}$/", $phone)) {
            return true;
        }
        return false;
    }

    /**
     * 获取完整的图片地址
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-9-27 下午02:48:12
     */
    public static function formatImageUrl($source_url){
        $target_url = '';

        if($source_url){
            if(strpos($source_url, '/') === 0){
                $target_url = HttpUtil::getBaseHost() . $source_url;
            }else{
                $target_url = $source_url;
            }
        }

        return $target_url;
    }

    /**
     * city没有主动传入，则从cookie中获取
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2016-10-29 上午10:43:30
     */
    public static function getCustomCity($city = ''){
        if($city == ''){
            $city = CookieUtil::get('custom_city');

            $city = StringUtil::getCityForEn($city);
        }

        return $city;
    }

    public static function getCityForCn($city = ''){
        $city = strtolower($city);
        if ($city == 'wh' || $city == 'wuhan'){
            $city = '武汉';
        }else if($city == 'nj' || $city == 'nanjing'){
            $city = '南京';
        }else{
            $city = '武汉';
        }

        return $city;
    }

    public static function getCityForEn($city = ''){
        if ($city == '武汉' || $city == 'wh' || $city == 'wuhan'){
            $city = 'wuhan';
        }else if($city == '南京' || $city == 'nj' || $city == 'nanjing'){
            $city = 'nanjing';
        }else{
            $city = 'wuhan';
        }

        return $city;
    }

    public static function processPhone($source){
        $source = substr_replace($source, '****', 3, 4);

        return $source;
    }

    // 输出xml字符
    public static function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }


    // xml 转object
    public static function xmlToObject($data)
    {
        if (!$data) {
            return false;
        } else {
            return simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
    }

    // 获取长度32的随机字符串
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    // 回复消息  text|news
    public static function responseMessage($reqMsg, $respMsg)
    {
        $resultStr = null;
        if ($reqMsg) {
            $to = $reqMsg->FromUserName;
            $from = $reqMsg->ToUserName;
            $time = time();
            header('Content-Type:text/xml', true);
            if ($respMsg['type'] == 'text') {
                $msg['text'] = "<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[%s]]></MsgType>
                                    <Content><![CDATA[%s]]></Content>
                                    <FuncFlag>0</FuncFlag>
                                </xml>";
                $resultStr = sprintf($msg['text'], $to, $from, $time, $respMsg['type'], htmlspecialchars_decode($respMsg['data']));
            }

            if ($respMsg['type'] == 'news') {
                $msg['newsitem'] = "<item>
                                        <Title><![CDATA[%s]]></Title>
                                        <Description><![CDATA[%s]]></Description>
                                        <PicUrl><![CDATA[%s]]></PicUrl>
                                        <Url><![CDATA[%s]]></Url>
                                    </item>";

                $msg['news'] = "<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[news]]></MsgType>
                                    <ArticleCount>%s</ArticleCount>
                                    <Articles>%s</Articles>
                                </xml>";
                $news = '';
                $newsCount = count($respMsg['data']);
                if ($newsCount) {
                    foreach ($respMsg['data'] as $new) {
                        $new['description'] = isset($new['description']) ? $new['description'] : '';
                        if (preg_match('/^\/uploads/', $new['img'])) {
                            $img_url = 'http://wan.deyi.com' . $new['img'];
                        } else {
                            $img_url = $new['img'];
                        }
                        $news .= sprintf($msg['newsitem'], $new['title'], $new['description'], $img_url, $new['to_url']);
                    }
                }
                $resultStr = sprintf($msg['news'], $to, $from, $time, $newsCount, $news);
            }
        }
        return $resultStr;
    }

    // 输入字符串过滤
    public static function filterString($str)
    {
        return htmlspecialchars($str);
    }

    /**
     * 数组转化为 XML格式字符串
     *
     * @param $params
     *
     * @return array|string
     */
    public static function arrayToXml($params)
    {

        if (!is_array($params) || !count($params)) {
            return array('status' => 0, 'message' => '参数不正确');
        }

        $xmlString = '';

        foreach ($params as $key => $val) {
            if (!is_numeric($key)) {
                if (!is_array($val)) {
                    $xmlString .= "<" . $key . ">" . $val . "</" . $key . ">";
                } else {
                    $value = self::arrayToXml($val);
                    $xmlString .= "<" . $key . ">" . $value . "</" . $key . ">";
                }
            } else {
                $value = self::arrayToXml($val);
                $xmlString .= $value;
            }
        }
        return $xmlString;
    }

    public static function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /*线上支付开发需要开启debug验证*/
    public static function getDebugOrderSn($order_sn, $type = 'wechat'){
        if(YII_PAY_DEV == true){
            if ($type == 'wechat'){
                $order_sn = 'WFT_test_wechat_' . $order_sn;
            }else{
                $order_sn = 'WFT_test_alipay_' . $order_sn;
            }
        }

        return $order_sn;
        //反解
        //wechat
        //$out_trade_no = str_replace('WFT_test_wechat_', '', $out_trade_no);

        //alipay
        //$out_trade_no = str_replace('WFT_test_alipay_', '', $out_trade_no);
    }
}