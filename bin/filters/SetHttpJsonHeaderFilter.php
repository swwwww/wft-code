<?php
/**
 * 设置统一的json http 头
 * @Description:
 * @ClassName: SetHttpJsonHeaderFilter
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2015-2-8 下午07:31:28
 */
class SetHttpJsonHeaderFilter extends CFilter{

    //filter前置方法
    protected function preFilter($filter_chain){
        header('Content-type:json/application;charset=utf-8');
        return true;
    }

    //filter后置方法
    protected function postFilter($filter_chain){

    }
}