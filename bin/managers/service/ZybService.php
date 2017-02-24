<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/8/19
 * Time: 15:00
 */
class ZybService extends Manager{

    function __construct()
    {
        $this->free_config = array(
            /*'front_notify_url' => 'http://mengxj.sendinfo.com.cn/boss/service/code.htm', //测试地址
            'free_name' => 'admin',
            'free_password' => 'TESTFX',
            'free_private_password' => 'TESTFX',*/
            'front_notify_url' => 'http://boss.zhiyoubao.com/boss/service/code.htm', //正式地址
            'free_name' => 'wftkj',
            'free_password' => 'sdzfxwftkj',
            'free_private_password' => '086111FE327503DD4EF9B4B3A646FE19',
        );
    }

    /**
     * 智游宝部分退票
     * @param $code_id //使用码id
     * @return array|mixed|string
     */

    public function backPartTicket($code_id) {

        //todo 退票情况查询 需要  退单批次号 retreatBatchNo
        $adapter = $this->_getAdapter();
        $order_data = $adapter->query("SELECT play_coupon_code.*, play_order_info.order_status, play_order_info.pay_status FROM play_coupon_code LEFT JOIN play_order_info ON play_order_info.order_sn = play_coupon_code.order_sn WHERE  play_coupon_code.id = ?", array($code_id))->current();
        if ($order_data->order_status != 1 || $order_data->pay_status < 2 || $order_data->status != 0) {
            return array('status' => 0, 'message' => '订单异常');
        }

        $config = $this->free_config;

        $data = array(
            'PWBRequest' => array(
                'transactionName' => 'RETURN_TICKET_NUM_NEW_REQ',
                'header' => array(
                    'application' => 'SendCode',
                    'requestTime' => date('Y-m-d', time()),
                ),
                'identityInfo' => array(
                    'corpCode' => $config['free_password'],
                    'userName' => $config['free_name'],
                ),
                'orderRequest' => array(
                    'returnTicket' => array(
                        'orderCode' => $order_data->id. $order_data->password,
                        'returnNum' => 1,
                        'thirdReturnCode' => 'WFT'. $order_data->order_sn,
                    ),
                ),
            ),
        );

        $xmlString = StringUtil::arrayToXml($data);

        if (is_array($xmlString)) {
            return $xmlString;
        }

        $result = $this->getResult($xmlString);

        return $result;

    }

    /**
     *  向智游宝发送请求
     * @param $xmlString
     * @return mixed
     */
    private function getResult($xmlString) {

        $config = $this->free_config;
        $sign = MD5('xmlMsg='. $xmlString. $config['free_private_password']);
        $xml = http_build_query(array("xmlMsg" => $xmlString,"sign" => $sign));
        $res_xml = HttpUtil::postXmlFileGetContents($xml, $config['front_notify_url']);
        $res =  StringUtil::xmlToArray($res_xml);
        return $res;
    }

    /**
     * 智游宝下单
     * @param $order_sn
     * @return array
     */
    public function pay($order_sn) {

        $config = $this->free_config;
        $account_type = 'vm'; //spot现场支付vm备佣金zyb智游宝支付
        $order_sn = (int)$order_sn;

        $adapter = $this->_getAdapter();
        $order_data = $adapter->query("SELECT play_order_info.*, play_order_otherdata.message FROM play_order_info LEFT JOIN play_order_otherdata ON play_order_otherdata.order_sn = play_order_info.order_sn WHERE  play_order_info.order_sn = ?", array($order_sn))->current();
        $good_info_data = $adapter->query("SELECT play_game_info.* FROM play_game_info LEFT JOIN  play_order_info_game ON play_order_info_game.game_info_id = play_game_info.id  WHERE play_order_info_game.order_sn = ?", array($order_sn))->current();

        if (!$order_data || !$good_info_data || $order_data->order_status != 1 || $order_data->pay_status < 2) {
            return array('status' => 0, 'message' => '订单异常');
        }

        $order_pay = bcadd($order_data->real_pay, $order_data->account_pay, 2);
        $user_name = $order_data->buy_name;
        $user_phone = $order_data->buy_phone;
        $ticket_price = $good_info_data->money;
        $remark = $order_data->message;
        $good_sm = $good_info_data->goods_sm;
        $codeXml = array();
        $code_data = $this->_getPlayCouponCodeTable()->fetchAll(array('order_sn' => $order_sn));

        //兼容时间 
        $occDate = 0;
        if (in_array($good_sm, array('20141126021218', '20141126021225', 'PST2015061001224', 'PST20150616013069'))) {
            $occDate = date('Y-m-d', time() + 86400);
        }

        foreach ($code_data as $code) {
            $codeXml[] = array(
                'ticketOrder' => array(
                    'orderCode' => $code->id . $code->password,
                    'price' => $ticket_price,
                    'quantity' => 1,
                    'totalPrice' => $order_data->coupon_unit_price,
                    'occDate' => $occDate ? $occDate : date('Y-m-d', time()), //当前时间下单
                    'goodsCode' => $good_sm,
                    'goodsName' => $order_data->coupon_name,
                    'remark' => $remark
                ),
            );
        }

        $xmlArr = array(
            'PWBRequest' => array(
                'transactionName' => 'SEND_CODE_REQ',
                'header' => array(
                    'application' => 'SendCode',
                    'requestTime' => date('Y-m-d', time()),
                ),
                'identityInfo' => array(
                    'corpCode' => $config['free_password'],
                    'userName' => $config['free_name'],
                ),
                'orderRequest' => array(
                    'order' => array(
                        'certificateNo' => '',
                        'linkName' => $user_name,
                        'linkMobile' => $user_phone,
                        'orderCode' => 'WFT'. $order_sn,
                        'orderPrice' => $order_pay,
                        'src' => '',
                        'groupNo' => '',
                        'payMethod' => $account_type,
                        'ticketOrders' => $codeXml,
                    ),
                ),
            ),
        );

        $xmlMsg = $this->arrayToXml($xmlArr);

        if (is_array($xmlMsg)) {
            return $xmlMsg;
        }

        $result = $this->getResult($xmlMsg);

        return $result;
    }

    /**
     * 获取订单信息 并更新时间
     * @param $order_sn
     * @return bool|int
     */
    public function getOrderInfo($order_sn) {

        $result = false;

        $Adapter = $this->_getAdapter();
        $res = $this->findOrderInfo($order_sn);
        if ($res['code'] == 0 && $res['description'] == '成功') {
            //这儿 以后不同日期 可以 不同处理
            if (isset($res['order']['scenicOrders']['scenicOrder'][0])) {
                $valueData = $res['order']['scenicOrders']['scenicOrder'][0];
            } else {
                $valueData = $res['order']['scenicOrders']['scenicOrder'];
            }

            $result = $Adapter->query('UPDATE play_zyb_info SET play_start_time = ?, play_end_time = ? WHERE order_sn = ?',array(strtotime($valueData['startDate']), strtotime($valueData['endDate']), $order_sn))->count();

        }

        return $result;

    }
}