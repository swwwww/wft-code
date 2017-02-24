<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/10/10 0010
 * Time: 下午 3:43
 */

class CouponController extends Controller
{

    public function filters()
    {
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    //我的现金券
    public function actionUserCoupon(){
        $coupon = new CouponLib();
        $res = $coupon -> getCashCouponLists($_POST);
        for($i=0, $use_len=0; $i<count($res['data']); $i++){
            if($res['data'][$i]['isvalid']!=0){
                ++$use_len;
            }
        }
        $data = $_GET;
        $res['data'] = json_encode($data);
        $res['use_len'] = $use_len;
//        DevUtil::e($res);exit();
        $this -> tpl -> assign('res', $res);
        $this->tpl->display('coupon/m/coupon.html');
    }

    //现金券列表
    public function actionGetCouponLists(){             
        $coupon = new CouponLib();
        HttpUtil::out($coupon -> getCashCouponLists($_POST));
    }

    //兑换现金券
    public function actionExchangeCoupon(){
        $coupon = new CouponLib();
        HttpUtil::out($coupon -> exchangeCashCoupon($_POST));
    }

    //现金券详情
    public function actionCouponDetail(){
        $coupon = new CouponLib();
        $in = array(
            'cid' => $_GET['cid'],
            'id' => $_GET['id']
        );

        $res_temp = $coupon->cashCouponDetails($in);
        $res = $res_temp['data'];

        $res['cid'] = $_GET['cid'];
        $res['id'] = $_GET['id'];

        $this -> tpl -> assign('res', $res);
        $this->tpl->display('coupon/m/coupon_detail.html');
    }

    public function actionCouponBuy(){
        $coupon = new CouponLib();
        HttpUtil::out($coupon -> cashCouponDetails($_POST));
    }

    //选择使用现金券
    public function actionCouponSelect(){
        $this->tpl->display('coupon/m/coupon_select.html');
    }

}