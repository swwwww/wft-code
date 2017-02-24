<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/24
 * Time: 09:44
 */
class WmsController extends Controller{
    /**
     * 订单相关操作
     * author: MEX | mixmore@yeah.net
     */
    public function actionPutOrder()
    {
        $service = new WmsService();
        $wechat_service = new WechatInteractService();

        $order_sn = intval($_POST['order_sn']);            // 传入订单id

        // 判断订单需要操作的类型
        $order_info = OrderInfoVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));

        if ($order_info) {
            $user = UserVo::model()->find('uid = :uid', array(':uid' => $order_info->user_id));
            $chain = WechatChainVo::model()->find('tyoe');
            // 使用switch进行操作判定 我们假设已经做到这一步了 现在就是购买成功发送消息
            // 根据订单 sn 判断是否有验证码
            $exercise_code = ExerciseCodeVo::model()->find('order_sn = :order_sn', array(':order_sn' => $order_sn));
            if ($exercise_code){
                // 存在验证码 意味着需要调用 带验证码的订单模板
                $content = $service->getTemplateContent($order_info, $user->open_id, $chain->url, $exercise_code->code);
            }else{
                $content = $service->getTemplateContent($order_info, $user->open_id, $chain->url);

            }
            $wechat_service->postTemplateMessage(json_encode($content));
        }
    }
}