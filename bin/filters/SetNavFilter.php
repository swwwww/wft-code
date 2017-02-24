<?php
/**
 * 统一设置导航栏的自定义filter
 * @Description:
 * @ClassName: SetNavFilter
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-7-7 下午08:29:08
 */
class SetNavFilter extends CFilter{
    public $controller;

    //在controller中指定
    public $nav_name;
    public $sub_nav_name;
    public $title;
    public $mobile_title;

    //filter前置方法
    protected function preFilter($filter_chain){
        $route = G::$param['route'];
        $tab_name = $route['c'];
        $nav_name = $this->nav_name == null ? $tab_name : $this->nav_name;

        $this->controller->tpl->assign('nav_name', $nav_name);
        $this->controller->tpl->assign('mobile_title', $this->mobile_title);
        $this->controller->tpl->assign('title', $this->title);

        return true;
    }

    //filter后置方法
    protected function postFilter($filter_chain){
    }
}