<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/11/4
 * Time: 01:53
 */
class SellerController extends Controller{
    //销售员中心
    public function actionSeller()
    {
        // 获取分销列表
        $in  = array(
            'page_num ' => 10,
            'page  '    => 1
        );
        $res = UserWapLib::sellerCenter($in);

        // 获取分销过程中的其他数据
        $service = new UserService();
        $user_vo = UserLib::getNowUser();

        $uid          = $user_vo['uid'];
        $account_data = $service->getSellerAccount($uid);

        $is_have = DistributionDetailVo::model()->find('sell_type = :sell_type' and 'sell_status = :sell_status' and 'sell_user_id = :uid', array(':sell_type' => 3, ':sell_status' => 1, ':uid' => $uid));

        $withdraw_now = $is_have ? 1 : 2;

        $data = array(
            'account_money'      => $account_data['account_money'],
            'add_up_income'      => $account_data['add_up_income'],
            'not_arrived_income' => $account_data['not_arrived_income'],
            'withdraw_cash'      => $account_data['withdraw_cash'],
            'can_out'            => 20,  // 最低提现金额
            'withdraw_now'       => $withdraw_now,
            'uid'                => $uid
        );
        $res['data']['seller'] = $data;
        $res['data']['seller'] = $data;
        $this->tpl->assign('res', $res);
        $this->tpl->display('user/m/user_seller.html');
    }

    //分销推广列表
    public function actionSellGoods()
    {
        $city = StringUtil::getCustomCity();
        $city = $city == 'wuhan' ? 'WH' : 'NJ';
        if (isset($_POST) && $_POST != NULL) {
            $data         = $_POST;
            $data['city'] = $city;
            HttpUtil::out(UserWapLib::sellGoods($data));
        } else {
            $user_vo = UserLib::getNowUser();
            $service = new UserService();

            $uid       = $user_vo['uid'];
            $seller_id = $_GET['seller_id'];

            if (!$service->isRight($seller_id)) {
                header("Location: /");
                exit;
            }

            $res = $service->getGoodsListCount($city);

            $is_seller = 0; //是否当前用户是分销员
            if ($uid && $service->isRight($uid)) {
                $is_seller = 1;
            }

            $data = array(
                'count_goods'    => $res['count_goods'],
                'count_activity' => $res['count_activity'],
                'seller_id'      => $seller_id,
                'is_seller'      => $is_seller,
            );
            $this->tpl->assign('res', $data);
            $this->tpl->display('user/m/sell_goods.html');
        }
    }

    public function actionGetAllSellInfo()
    {
        $lib = new UserWapLib();
        HttpUtil::out($lib->sellerCenter($_POST));
    }

    public function actionGetAllSellCash()
    {
        $lib = new UserWapLib();
        HttpUtil::out($lib->getCash($_POST));
    }

    public function actionGetAllSellGoods()
    {
        $lib = new UserWapLib();
        HttpUtil::out($lib->sellGoods($_POST));
    }
}