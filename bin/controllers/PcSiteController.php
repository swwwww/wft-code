<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/12/28 0028
 * Time: 上午 11:22
 */

class PcSiteController extends Controller{

    public function filters(){
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    // 主站遛娃学院
    public function actionSchool(){
        $this->tpl->display('pc/school.html');
    }
    //主站用户版
    public function actionIndex(){
        $this->tpl->display('pc/user_index.html');
    }
    //主站商家
    public function actionBusiness(){
        $this->tpl->display('pc/business.html');
    }
    //join
    public function actionJoin(){
        $this->tpl->display('pc/merchant_join.html');
    }
    //文章列表
    public function actionArticle(){
        $this->tpl->display('pc/article_list.html');
    }
}