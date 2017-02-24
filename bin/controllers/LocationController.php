<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/18
 * Time: 11:47
 */
class LocationController extends Controller{
    public function actionShowMap(){
        $this->tpl->display('location/show_map.html');
    }

    public function actionTest(){
        $phone = $_GET['phone'];
        if(StringUtil::checkPhone($phone)){

            $param = array(
                'name' => 'qintao',
                'code' => '123123',
            );
            var_dump($this->alisms->send($phone, $param));
            var_dump('发送验证码成功');
        }else{
            echo '手机号码错误';
        }
    }

    /**
     * doc 用于识别用户地理位置进行相应商品或者活动的位置跳转
     *
     * URL  location/redirect
     * http://play.wanfantian.com/location/redirect?wh=http://wan.wanfantian.com/web/coupon/newactivity?id=503&nj=http://wan.wanfantian.com/web/organizer/shops?id=1712
     *
     * author: MEX | mixmore@yeah.net
     */
    public function actionRedirect()
    {
        $city_arr = array(
        0 => array(
                'city' => '武汉市',
                'id'   => 'wuhan',
                'url'  => $_GET['wh']
        ),
        1 => array(
                'city' => '南京市',
                'id'   => 'nanjing',
                'url'  => $_GET['nj']
        )
        );

        $this->tpl->assign('title', '玩翻天正在给您定位');
        $this->tpl->assign('city_arr', $city_arr);
        $this->tpl->display('location/index.html');
    }

    public function actionRedirectHome(){
        $date = TimeUtil::getNowDate();
        $stats_clicks = StatsClicksVo::model()->find('log_date = :log_date and type = :type', array(':log_date' => $date, ':type' => 1));
        if (!$stats_clicks){
            $stats_clicks = new StatsClicksVo();
            $stats_clicks->log_date = $date;
            $stats_clicks->type = 1;
            $stats_clicks->name = '首页';
        }
        $stats_clicks->clicks += 1;
        $stats_clicks->save();

        $url = "http://wan.wanfantian.com/web/wappay/nindex";
        Yii::app()->request->redirect($url);
    }

    public function actionOpenPage(){
        $type_arr = array('goods', 'place', 'special', 'merchant', 'user');

        $type = $_GET['type'];
        $id = intval($_GET['id']);

        if(!in_array($type, $type_arr)){
            $type = 'place';
        }

        $data = array(
            'type' => $type,
            'id' => $id,
        );

        $this->tpl->assign('title', '玩翻天正在努力加载中');
        $this->tpl->assign('page_data', $data);
        $this->tpl->display('location/open_page.html');
    }
}