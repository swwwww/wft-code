<?php
/**
 * Created by IntelliJ IDEA.
 * User: deyi
 * Date: 2016/8/5
 * Time: 9:36
 */

class DiscoverController extends Controller{
    public function filters(){
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    /* *
     *  发现主模块
     * */
    /*发现首页*/
    public function actionDiscoverDetail(){
        $lib = new DiscoverLib();
        $in = array(
            'come' => 2
        );
        $res_temp = $lib->discoverList($in);
        $res = $res_temp['data'];
        $this->tpl->assign('res', $res);
        $this->tpl->display('discover/m/discover_detail.html');
    }

    /*专题列表*/
    public function actionSpecialList(){
        $lib = new DiscoverLib();
        $in = array(
            'page' => 1,
            'page_num'=> 5
        );
        $res_temp = $lib->topicList($in);
        $res = $res_temp['data'];
        $this->tpl->assign('res',$res);
        $this->tpl->display('discover/m/special_list.html');
    }

    /*专题详情页*/
    public function actionSpecialInfo(){
        $lib = new DiscoverLib();
        $in =array(
            'id' => $_GET['id'],
            'order' => 'hot',
            'show' => '',
        );
        $res_temp = $lib->topicInfo($in);
        $res = $res_temp['data'];
        $this->tpl->assign('res',$res);
        $this->tpl->display('discover/m/special_info.html');
    }

    /*游玩地列表*/
    public function actionPlayList(){
        $lib = new DiscoverLib();
        $res_temp = $lib->playList();
        $res = $res_temp['data'];
        $this->tpl->assign('res',$res);
        $this->tpl->display('discover/m/play_list.html');
    }

    /*游玩地详情*/
    public function actionPlayDetail(){
        $lib = new DiscoverLib();
        $id = intval($_GET['id']);

        $in=array(
            'id' => $id,
        );
        $res_temp =$lib->playDetail($in);
        $res = $res_temp['data'];
        $res['id'] = $id;
        $res['flag_good'] = true;
        if (count($res['good_list'])==0){
            $res['flag_good'] = false;
        }
        $shop_strategy_vo = ShopStrategyVo::model()->find('sid = :sid and status>0', array(':sid' => $id));
        $res['information'] = htmlspecialchars_decode($shop_strategy_vo->information);
        $res['strategy_id'] = $shop_strategy_vo->id;

        // 判断是否收藏
        $user_vo = UserLib::getNowUser();
        $res['is_collect'] = 0;
        if ($user_vo){
            $uid = $user_vo['uid'];
            $is_collect = UserCollectVo::model()->find("uid = {$uid} and type = 'shop' and link_id = {$id}");
            if ($is_collect){
                $res['is_collect'] = 1;
            }
        }
        $collect_data = array(
            'id' => $id,
            'type' => 'shop'
        );
        $res['collect_data'] = json_encode($collect_data);

        $this->tpl->assign('res',$res);
        $this->tpl->display('discover/m/play_detail.html');
    }

    public function actionGetAllSpecial()
    {
        $lib = new DiscoverLib();
        HttpUtil::out($lib->topicList($_POST));
    }

    public function actionGetAllInfo()
    {
        $lib = new DiscoverLib();
        HttpUtil::out($lib->topicInfo($_POST));
    }

    public function actionGetAllPlayground()
    {
        $lib = new DiscoverLib();
        HttpUtil::out($lib->playList($_POST));
    }

    public function actionGetAllPlayDetail()
    {
        HttpUtil::out(DiscoverLib::playDetail($_POST));
    }

    public function actionGetAllPostLike()
    {
        $lib = new DiscoverLib();
        HttpUtil::out($lib->postLike($_POST));
    }

    public function actionGetAllPostDel()
    {
        $lib = new DiscoverLib();
        HttpUtil::out($lib->postDel($_POST));
    }
}