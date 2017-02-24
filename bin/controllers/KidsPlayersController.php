<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/12/27 0027
 * Time: 下午 3:27
 */
class KidsPlayersController extends Controller{

    public function filters(){
        return array(array(
            'application.filters.SetNavFilter',
            'mobile_title' => '精选',
            'controller' => $this,
        ));
    }

    // 活动列表(首页)获取 OK
    public function actionkidsPlayersInstr(){
        $service = new KidsPlayersService();
        $res['players'] = $service->kidsPlayersPage();
        
        $this->tpl->assign('res', $res);
        $this->tpl->display('kidsplayers/m/kidsplayers_instruction.html');
    }
}




