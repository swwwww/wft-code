<?php
/**
 * 模拟数据类，用于前端前后端分离开发
 */
class MockController extends Controller{

    public function filters(){
        return array(
        array(
            'application.filters.SetHttpJsonHeaderFilter',
        ),
        );
    }

    public function actionTest(){
        echo json_encode(
            array(
                array(
                    'name' => '啊啊',
                    'value' => 1
                ),
                array(
                    'name' => '拜拜拜',
                    'value' => 2
                )
            )
        );
    }
}
