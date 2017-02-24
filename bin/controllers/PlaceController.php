<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 上午 10:42
 */
class PlaceController extends Controller{

    public function filters(){
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    public function actionPlaceIndex(){
        $lib = new PlaceLib();
        $data = new PlaceData();

        $in = array(
            'id' => 1234
        );
        $res_temp = $lib->PlaceIndex($in);
        $res = $res_temp['data'];
        //        $res['whole_score_num'] = $res['whole_score'];
        //        $res['whole_score'] =  ($res['whole_score_num']/5)*100.%

        $query_res = $data->getShopStrategyInfo($in['id']);
        $res['information'] = htmlspecialchars_decode($query_res['information']);

        $res['strategy_id'] = $query_res['strategy_id'];
        //        DevUtil::e($res);exit();

        $this->tpl->assign('res', $res);
        $this->tpl->display('play/m/play_detail.html');
    }





    public function actionGetAllPlace(){

        $lib = new PlaceLib();
        httpUtil::out($lib->placeList($_POST));

        /* $result = HttpUtil::getHttpResult();
         echo json_encode($result);*/

    }
}