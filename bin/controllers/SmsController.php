<?php
/**
 * Created by PhpStorm.
 * short message service
 * 短信服务
 *
 * User: MEX
 * Date: 16/7/16
 * Time: 14:06
 */
class SmsController extends Controller{
    public function actionGetCode(){
        $phone = trim($_POST['phone']);

        $param = array(
            'phone' => $phone,
        );
        $result = SmsLib::getCode($param);

        HttpUtil::out($result);
    }
    /**
     * 获取验证码
     * author: MEX | mixmore@yeah.net
     */
    public function actionGetCodeBak()
    {
        $result = array(
            'status' => 0,
            'data'   => array(),
            'msg'    => '',
        );
        // 验证手机号是否合法
        $phone = trim($_POST['phone']);
        if (StringUtil::checkPhone($phone)) {
            // 手机号合法
            // 避免同一用户并发
            if (CacheUtil::getX('send' . $phone)) {
                // 间隔时间过短
                $result['status'] = 0;
                $result['msg'] = '发送频率过高，请稍后再试';
            } else {
                CacheUtil::setX('send' . $phone, time(), 2);
                // 一天内 只允许5条,每条相隔1分钟
                $time = time() - 43200; // 5天之内
                $service = new SmsService();
                $data = $service->getUserAuthCode($phone, $time);
                if (!empty($data) and (time() - $data[0]['time']) < 59) {
                    $result['status'] = 0;
                    $result['msg'] = '发送频率过高，请稍后再试';
                } elseif (count($data) == 15) {
                    $result['status'] = 0;
                    $result['msg'] = '超过每日短信限制';
                } else {
                    //发送验证码
                    $code = SmsUtil::sendAuthCode($phone);
                    if ($code) {
                        $giftVo = new AuthCodeVo();
                        $giftVo['phone'] = $phone;
                        $giftVo['time'] = time();
                        $giftVo['status'] = 0;
                        $giftVo['code'] = $code;
                        $giftVo->save();
                        $result['status'] = 1;
                        $result['msg'] = '发送成功';
                    } else {
                        $result['status'] = 0;
                        $result['msg'] = '发送失败';
                    }
                }
            }
        } else {
            // 手机号不合法
            $result['status'] = 0;
            $result['msg'] = '手机号不合法';
        }
        HttpUtil::out($result);
    }
}
