<?php
/**
 * 微信公众号 接口服务
 * @classname: WechatInterfaceService
 * @author 11942518@qq.com | quenteen
 * @date 2015-9-15
 */
class WechatInterfaceService extends Manager{

    public $user_vo = null;

    public $msg_type_obj = array(
        'text',
        'img',
        'event',
    );

    public function __construct($user_vo){
        $this->user_vo = $user_vo;
    }

    public function run(){
        if($this->user_vo == null){
            $token = '9527';
            $status = 'call';
        }else{
            $token = $domain_user['wechat_token'];
            $status = $domain_user['wechat_status'];//check | call
        }

        //通过微信config来确定现在是验证模式还是反馈模式
        if($status == 'check'){
            $this->checkWechat($token);
        }else{
            $this->callWechat();
        }
    }

    public function callText($post_arr, $from_user_name = null){
        $msg_content = trim($post_arr['Content']);
        $msg_content = trim($msg_content);
        $msg_id = $post_arr['MsgId'];

        switch($msg_content){
            case '1':
            case '数字1':
            case 'a':
            case 'A':
                $send_content_str = WechatUtil::getWay($from_user_name);
                break;
            case '2':
            case 'b':
            case 'B':
                $send_content_str = WechatUtil::getPurpose();
                break;
            case '3':
            case 'c':
            case 'C':
                $send_content_str = WechatUtil::getVision();
                break;
            case '4':
            case 'd':
            case 'D':
                $send_content_str = WechatUtil::getEntry();
                break;
            default:
                $send_content_str = WechatUtil::getSubscribeMsg();
                break;
        }

        $result = array(
            'msg_content' => $msg_content,
            'send_content' => $send_content_str,
        );

        return $result;
    }

    public function callImage($post_arr){
        $pic_url = $post_arr['PicUrl'];
        $media_id = $post_arr['MediaId'];

        $result = array(
            'pic_url' => $pic_url,
            'media_id' => $media_id,
        );

        return $result;
    }

    public function callEvent($post_arr){
        $event_type = $post_arr['Event'];
        $msg_id = $post_arr['FromUserName'] . '#!#' . $create_time;

        //方式为：订阅
        if($event_type == 'subscribe'){
            $send_content_str = WechatUtil::getSubscribeMsg();
        }

        $result = array(
            'msg_id' => $msg_id,
            'send_content' => $send_content_str,
        );

        return $result;
    }

    public function callWechat(){
        //get post data, maybe due to the different env
        $source_msg = $GLOBALS['HTTP_RAW_POST_DATA'];

        if(!empty($source_msg)){
            libxml_disable_entity_loader(true);
            $post_obj = simplexml_load_string($source_msg, 'SimpleXMLElement', LIBXML_NOCDATA);
            $post_arr = (array)$post_obj;

            $from_user_name = $post_arr['FromUserName'];
            $to_user_name = $post_arr['ToUserName'];
            $create_time = $post_arr['CreateTime'];
            $msg_type = $post_arr['MsgType'];

            switch($msg_type){
                case 'text':
                    $call_result = $this->callText($post_arr, $from_user_name);
                    $send_content_str = $call_result['send_content'];
                    break;
                case 'image':
                    $call_result = $this->callImage($post_arr);
                    $send_content_str = WechatUtil::getSubscribeMsg();
                    break;
                case 'event':
                    $call_result = $this->callEvent($post_arr);
                    $send_content_str = $call_result['send_content'];
                    break;
                default:
                    break;
            }

            $detail_arr['from_user_name'] = $from_user_name;
            $detail_arr['to_user_name'] = $to_user_name;
            $detail_arr['create_time'] = $create_time;
            $detail_arr['msg_type'] = $msg_type;
            $detail_arr['msg_id'] = $call_result['msg_id'];
            $detail_arr['msg_content'] = $call_result['msg_content'];
            $detail_arr['media_id'] = $call_result['media_id'];
            $detail_arr['pic_url'] = $call_result['pic_url'];
            $detail_arr['event_type'] = $event_type;

            //获取发送内容
            $send_content_as_xml = $this->feedbackWechat($detail_arr, $send_content_str);

            //记录log
            $this->wechatLog($detail_arr, $send_content_str, $post_arr);

            echo $send_content_as_xml;
        }else{
            echo 'no post';
        }
    }

    public function feedbackWechat($detail_arr, $send_content_str){
        $send_msg_type = 'text';
        $send_content_as_xml = $this->combineContent($detail_arr, $send_content_str, $send_msg_type);

        return $send_content_as_xml;
    }

    //回馈微信：验证服务器是否正确部署
    public function checkWechat($token){
        $echo_str = $_GET['echostr'];

        if($this->signWechat($token)){
            echo $echo_str;
        }else{
            echo 'wechat fail';
        }
    }

    public function combineContent($detail_arr, $send_content_str, $send_msg_type = 'text'){
        $from_user_name = $detail_arr['from_user_name'];
        $to_user_name = $detail_arr['to_user_name'];
        $create_time = $detail_arr['create_time'];
        $msg_type = $detail_arr['msg_type'];
        $event_type = $detail_arr['event_type'];
        $msg_id = $detail_arr['msg_id'];
        $msg_content = $detail_arr['msg_content'];

        $send_text_tpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";

        $time = time();

        $send_text_tpl = str_replace(" ", '', $send_text_tpl);
        $send_text_tpl = str_replace("\t", '', $send_text_tpl);
        $send_content_as_xml = sprintf($send_text_tpl, $from_user_name, $to_user_name, $time, $send_msg_type, $send_content_str);

        return $send_content_as_xml;;
    }

    public function wechatLog($detail_arr, $target_msg, $post_arr){
        try{
            $log = new WechatLogVo();
            $log->from_user_name = $detail_arr['from_user_name'];
            $log->to_user_name = $detail_arr['to_user_name'];
            $log->event_type = $detail_arr['event_type'];
            $log->msg_type = $detail_arr['msg_type'];
            $log->msg_id = $detail_arr['msg_id'];
            $log->msg_content = $detail_arr['msg_content'];
            $log->pic_url = $detail_arr['pic_url'];
            $log->media_id = $detail_arr['media_id'];
            $log->source_msg = json_encode($post_arr);
            $log->target_msg_type = 'text';
            $log->target_msg = $target_msg;
            $log->created = TimeUtil::getNowDateTime();
            $log->save();
        }catch(Exception $e){

        }
    }

    //验证微信token是否正确
    public function signWechat($token = '9527'){
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];

        $tmp_arr = array($token, $timestamp, $nonce);

        //use sort_string rule
        sort($tmp_arr, SORT_STRING);
        $tmp_str = implode($tmp_arr);
        $tmp_str = sha1($tmp_str);

        $signature = $_GET['signature'];

        if($tmp_str == $signature){
            return true;
        }else{
            return false;
        }
    }

}