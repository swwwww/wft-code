<?php
class MainController extends Controller{

    public function filters(){
        $route = G::$param['route'];

        $tab_name = $route['c'];

        return array(
        array(
            'application.filters.SetNavFilter',
            'nav_name' => $tab_name,
            'mobile_title' => '关于我',
            'controller' => $this,
        ),
        );
    }

    public function actionIndex(){
        $this->tpl->display('demo/index.html');
        //$this->tpl->display('about/about.html');
    }

    public function actionDemo(){
        //编码、解码
        $str = json_encode(array(
            'uid' => 1,
            'token' => '88bb76c452b33c7de5d1b30c975c2ad3',
        ));
        $target = SecretUtil::encrypt($str);
        $source = SecretUtil::decrypt($target);

        //app请求的参数
        $param = $_POST['p'];

        $check_flag = AuthManage::check($target);
        var_dump($check_flag);
    }
}