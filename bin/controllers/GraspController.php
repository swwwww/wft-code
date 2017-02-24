<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/9/22 0022
 * Time: 下午 5:48
 */

class GraspController extends Controller{

    //首页
    public function actionGameIndex()
    {
        $this->tpl->display('ad/grasp/game_index.html');
    }
    public function actionStartGame()
    {
        $this->tpl->display('ad/grasp/start_game.html');
    }
    //游戏主页
    public function actionDuringGame()
    {
        $this->tpl->display('ad/grasp/get_child.html');
    }
    //抽奖页面
    public function actionGetPrise()
    {
        $this->tpl->display('ad/grasp/get_prise.html');
    }
    //我的奖品页面
    public function actionMyPrise()
    {
        $this->tpl->display('ad/grasp/my_prise.html');
    }

    //三个样例
    public function actionIndex()
    {
        $this->tpl->display('ad/grasp/exp/index.html');
    }
    public function actionGame()
    {
        $this->tpl->display('ad/grasp/exp/game.html');
    }

    public function actionGameOver()
    {
        $this->tpl->display('ad/grasp/exp/gameover.html');
    }
}
