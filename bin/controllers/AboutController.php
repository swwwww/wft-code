<?php
class AboutController extends Controller{


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
        //$user = new UserMongo();
        //$user->id = 1;
        //$user->save();

        $scrollTopValue = $_GET['scroll'];
        $this->tpl->assign('scrollValue', intval($scrollTopValue));
        $this->tpl->display('about/about.html');
    }
}