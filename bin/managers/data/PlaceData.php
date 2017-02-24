<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 上午 11:49
 */
class PlaceData extends Manager{
    public function getShopStrategyInfo($id){
        $sql = "select information,id from play_shop_strategy where sid={$id} and status>0 order by status desc,id desc limit 1";
        $query = Yii::app()->db->createCommand($sql)->queryRow();
        $res['information'] = $query['information'];
        $res['strategy_id'] = $query['id'];
        return $res;
    }

}