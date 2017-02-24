<?php
Yii::import('application.modules.api.controllers.finance.*');
class FinanceController extends Controller {

    /**
     * filter的两种写法
     */
    public function filters(){
        $route = G::$param['route'];

        $tab_name = $route['c'];

        return array(
            'httpHeader + get',
            array(
                'application.filters.SetHttpJsonHeaderFilter - get',
            ),
        );
    }

    public function filterHttpHeader($filter_chain){
        $filter_chain->run();
    }

    /**
     * 配置action
     */
    public function actions(){
        return array(
            'get' => array('class' => 'GetAction'),
        );
    }
}

