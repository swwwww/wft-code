<?php
/**
 * demo 控制器
 * @classname: IndexController
 * @author 11942518@qq.com | quenteen
 * @date 2016-6-27
 */
class BusinessController extends Controller {

    public function actionTest(){
        echo 'Hello new module!';
        $result = BusinessData::getCardInfo();
        var_dump($result);
    }

    //主站商家版
    public function actionBusiness(){
        $this->tpl->display('admin/business/business.html');
    }

    //登录
    public function actionPostLogin(){
        $result = HttpUtil::getHttpResult();
        $data = BusinessData::postBusinessLogin($_POST);
        if($data['status'] != 0){
            $login_vo = $data['data'];
            $organizer_id = $login_vo['organizer_id'];
            $rc_auth = $login_vo['rc_auth'];

            $user_vo = UserData::getBusinessUserById($organizer_id);

            if($user_vo){
                $user_vo['organizer_id'] = $organizer_id;
                $user_vo['rc_auth'] = $rc_auth;

                UserLib::setBusinessNowUser($user_vo);

                $result['status'] = 1;
            }
        }
        
        HttpUtil::out($result);
    }
    //退出登录
    public function actionLogout(){
        Yii::app()->session->destroy();

        //$callback_url = $_SERVER['HTTP_REFERER'];
        $base_host = HttpUtil::getBaseHost();
        $callback_url = $base_host . '/admin/business/business';

        JumpUtil::go($callback_url);
    }
    //首页
    public function actionIndex(){
        $on_sale         = intval($_GET['on_sale']) ? intval($_GET['on_sale']) : 1;
        $page            = intval($_GET['page']) ? intval($_GET['page']) : 1;

        $data = array(
            'page'     => $page,
            'page_num' => 10,
            'on_sale'  => $on_sale,
        );

        $result = BusinessData::postIndex($data);
        $result['data']['nav'] = 'index';
        $page_count = ceil($result['data']['count_data']['count_num']/$data['page_num']);
        $result['data']['page_count'] = $page_count;
        $result['data']['param'] = $data;
        $page_info = array(
            'current'    => $page,
            'state'      => $on_sale,
            'page_count' => $page_count,
            'count_num'  => $result['data']['count_data']['count_num']
        );
        $result['data']['page_info'] = json_encode($page_info);
        $this->tpl->assign('res', $result['data']);
        $this->tpl->display('admin/business/mer_index.html');
    }

    //验证验证码接口
    public function actionCheckCode(){
        HttpUtil::out(BusinessData::checkCode($_POST));
    }

    //确认验证码接口
    public function actionConfirmCode(){
        HttpUtil::out(BusinessData::confirmCode($_POST));
    }

    //商家入驻
    public function actionApplicationIn(){
        $this->tpl->display('admin/business/mer_application_in.html');
    }
    //请求接口
    public function actionPostSellInfo(){
        $result = BusinessData::postMerchantInfo($_POST);
        HttpUtil::out($result);
    }

    //忘记密码
    public function actionSetPassword(){
        $result['type'] = intval(0);
        $this->tpl->assign('res', $result);
        $this->tpl->display('admin/business/mer_password.html');
    }
    //获得验证码的接口
    public function actionGetVerifyCode(){
        HttpUtil::out(BusinessData::getPhoneCode($_POST));
    }
    //找回密码的接口
    public function actionGetPassword(){
        $result = BusinessData::getLoginPassword($_POST);
        HttpUtil::out($result);
    }

    //修改密码
    public function actionChangePassword(){
        $result['type'] = intval(1);
        $this->tpl->assign('res', $result);
        $this->tpl->display('admin/business/mer_password.html');
    }
    //修改密码的接口
    public function actionPostPasswordChange(){
        HttpUtil::out(BusinessData::changePassword($_POST));
    }

    //银行卡号管理
    public function actionManageCard(){
        $in = array(
            'organizer_id ' => $_COOKIE['organizer_id'],
            'rc_auth' =>  $_COOKIE['rc_auth']
        );
        $result = BusinessData::getCardInfo($in);

        $this->tpl->assign('res', $result['data']);
        $this->tpl->display('admin/business/mer_manage_card.html');
//        if($result['status'] == 1){
//            $this->tpl->assign('res', $result['data']);
//            $this->tpl->display('admin/business/mer_manage_card.html');
//        }
    }
    //修改银行卡号
    public function actionUpdateBankCard(){
        HttpUtil::out(BusinessData::postUpdateCardInfo($_POST));
    }

    //财务管理
    public function actionTradeFlow(){
        $data = new BusinessData();
        $page = $_GET['page']? $_GET['page'] : 1;
        $time_start = $_GET['time_start'];
        $time_end   = $_GET['time_end'];
        $object_type = $_GET['object_type'];

        $in = array(
            'page' => $page,
            'page_num' => 10,
            'time_start' => $time_start,
            'time_end' => $time_end,
            'object_type' => $object_type,
        );

        $result = $data->postTradeFlow($in);
        $page_count = ceil($result['data']['count_num']/$in['page_num']);
        $page_info = array(
            'current'    => $page,
            'page_count' => $page_count,
            'count_num'  => $result['data']['count_num']
        );
        $result['data']['page_info'] = json_encode($page_info);

        //交易类型
        $result['data']['object_type'] = array(
            '1' => '订单分润',
            '2' => '商家提现',
            '3' => '预付款',
            '4' => '特殊退款',
            '5' => '订单冲账',
            '6' => '特殊结算'
        );
        $result['data']['post_data'] = $in;
        $result['data']['nav'] = 'tradeFlow';

        $this->tpl->assign('res', $result['data']);
        $this->tpl->display('admin/business/mer_trade_flow.html');
    }

    //订单管理
    public function actionManageOrder(){
        $data = new BusinessData();
        $page = $_GET['page']? $_GET['page']: 1;
        $code = $_GET['code'];
        $order_sn = $_GET['order_sn'];
        $coupon_name = $_GET['coupon_name'];
        $time_start = $_GET['time_start'];
        $time_end   = $_GET['time_end'];
        $sort_type  = $_GET['sort_type'];
        $view_type   = $_GET['view_type']? $_GET['view_type']:2;
        $in = array(
            'page' => $page,
            'page_num' => 10,
            'code'     => $code,
            'order_sn' => $order_sn,
            'coupon_name' => $coupon_name,
            'time_start' => $time_start,
            'time_end' => $time_end,
            'sort_type' => $sort_type, //1购买时间排序 2验证时间排序
            'view_type' => $view_type, // 1所有 2 已使用 3待使用 4退款
        );
        $result = $data->postOrderList($in);

        $result['data']['post_data'] = $in;
        $result['data']['nav'] = 'manageOrder';
        $result['data']['organizer_name'] = $_COOKIE['organizer_name'];

        //排序规则
        $result['data']['object_type'] = array(
            '1' => '购买时间排序',
            '2' => '验证时间排序'
        );
        //处理列表, view_type 默认传2
        if($view_type == 1){
            $result['data']['list'] = $result['data']['all_list'];
            $result['data']['all_list'] = '';
            $result['data']['count_num'] = $result['data']['count']['all_list_count'];
        }else if($view_type == 2 || $view_type == ''){
            $result['data']['list'] = $result['data']['use_list'];
            $result['data']['count_num'] = $result['data']['count']['use_list_count'];
            $result['data']['use_list'] = '';
        }else if($view_type == 3){
            $result['data']['list'] = $result['data']['wait_list'];
            $result['data']['count_num'] = $result['data']['count']['wait_list_count'];
            $result['data']['wait_list'] = '';
        }else if($view_type == 4){
            $result['data']['list'] = $result['data']['back_list'];
            $result['data']['count_num'] = $result['data']['count']['back_list_count'];
            $result['data']['back_list'] = '';
        }

        //取页码
        $page_count = ceil($result['data']['count_num']/$in['page_num']);
        $page_info = array(
            'current'    => $page,
            'page_count' => $page_count,
            'count_num'  => $result['data']['count_num'],
            'view_type'  => $view_type
        );
        $result['data']['page_info'] = json_encode($page_info);

        $this->tpl->assign('res', $result['data']);
        $this->tpl->display('admin/business/mer_manage_order.html');
    }
    //导出文件
    public  function actionGetOrderExecl(){
        $data = new BusinessData();
        $user_vo = UserLib::getBusinessNowUser();

        $page = $_GET['page']? $_GET['page']: 1;
        $code = $_GET['code'];
        $order_sn = $_GET['order_sn'];
        $coupon_name = $_GET['coupon_name'];
        $time_start = $_GET['time_start'];
        $time_end   = $_GET['time_end'];
        $sort_type  = $_GET['sort_type'];
        $view_type   = $_GET['view_type']? $_GET['view_type']:2;
        $in = array(
            'page' => $page,
            'page_num' => 10,
            'code'     => $code,
            'order_sn' => $order_sn,
            'coupon_name' => $coupon_name,
            'time_start' => $time_start,
            'time_end' => $time_end,
            'sort_type' => $sort_type, //1购买时间排序 2验证时间排序
            'view_type' => $view_type, // 1所有 2 已使用 3待使用 4退款
            'organizer_id' => $user_vo['organizer_id'],
            'rc_auth' => $user_vo['rc_auth']
        );
        $result = $data->getOrderFile($in);

    }


    //商品详情
    public function actionGoodsDetail(){
        $data = new BusinessData();
        $user_vo = UserLib::getBusinessNowUser();

        $coupon_id = $_GET['coupon_id'];
        $type = $_GET['type'];
        $page = $_GET['page']? $_GET['page'] : 1;
        $in = array(
            'coupon_id'=> $coupon_id,
            'page'     => $page,
            'page_num' => 10,
            'organizer_id' => $user_vo['organizer_id'],
            'rc_auth' => $user_vo['rc_auth']
        );

        if($type == ''){
            $result = $data -> getGoodDetails($in);
            $result['data']['coupon_information'] = htmlspecialchars_decode($result['data']['coupon_information']);
        }else if($type == 1){
            $result = $data -> getGoodsSeries($in);

            //取页码
            $page_count = ceil($result['data']['count_num']/$in['page_num']);
            $page_info = array(
                'type' =>  $type,
                'coupon_id'=> $coupon_id,
                'current'    => $page,
                'page_count' => $page_count,
                'count_num'  => $result['data']['count_num']
            );
            $result['data']['page_info'] = json_encode($page_info);

        }

        $result['data']['coupon_id'] = $coupon_id;
        $result['data']['type'] = $type;

        $this->tpl->assign('res', $result['data']);
        $this->tpl->display('admin/business/mer_goods_detail.html');
    }
}

