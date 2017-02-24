<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 4:20
 */

class AskController extends Controller{

    //遛娃活动咨询列表
    public function actionPlayAsk(){
        $lib = new AskLib();

        $in = array(
            'play_id' => $_GET['play_id']
        );
        $res_play_ask = $lib->PlayAsk($in);
        $res_play_ask_list = $lib->PlayAskList($in);
        $result['ask'] = $res_play_ask['data'];
        $result['ask_list'] = $res_play_ask_list['data'];
//        DevUtil::e($result);exit();
        $this->tpl->assign('res', $result);
        $this->tpl->display('play/m/play_consult.html');
    }

    public function actionGetAllAsk()
    {
        $lib = new AskLib();
        // todo 优化接收参数的方式
        HttpUtil::out($lib->PlayAsk($_POST));
        HttpUtil::out($lib->PlayAskList($_POST));
    }
}