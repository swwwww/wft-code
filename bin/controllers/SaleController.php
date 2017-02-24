<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/10/19 0022
 * Time: ���� 5:48
 */

class SaleController extends Controller{

    //双十一活动首页
    public function actionSaleIndex()
    {
        $this->tpl->display('ad/sale/sale_index.html');
    }

    public function actionSaleIndexNj()
    {
        $this->tpl->display('ad/sale/sale_index_nj.html');
    }
    //双十一活动游戏页
    public function actionSaleGame()
    {
        $this->tpl->display('ad/sale/sale_game.html');
    }
    //双十一奖品页
    public function actionSalePrise()
    {
        $this->tpl->display('ad/sale/sale_prise.html');
    }

    public function actionBattleIndex()
    {
        $this->tpl->display('ad/battle/battle_index.html');
    }
    public function actionBattleGame()
    {
        $this->tpl->display('ad/battle/battle_game.html');
    }
    public function actionBattlePrise()
    {
        $this->tpl->display('ad/battle/battle_prise.html');
    }

    public function actionBattleResult()
    {
        $this->tpl->display('ad/battle/battle_result.html');
    }
    public function actionBattleMy()
    {
        $this->tpl->display('ad/battle/battle_my.html');
    }

}
