<?php

/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/11/9 0009
 * Time: 下午 7:22
 */
class CashController extends Controller
{

    public function filters()
    {
        return array(array(
                         'application.filters.SetNavFilter',
                         'mobile_title' => '精选',
                         'controller'   => $this,
                     ));
    }

    /*刮奖首页*/
    public function actionCashIndex()
    {
        $this->tpl->display('cash/m/cash_index.html');
    }
}