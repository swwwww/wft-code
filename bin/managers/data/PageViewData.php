<?php
/**
 * 页面uv数据操作层
 * @classname: PageViewData
 * @author 11942518@qq.com | quenteen
 * @date 2016-6-30
 */
class PageViewData extends Manager{

    public static function getPv($name, $channel){
        $pv_vo = PageViewVo::model()->find("name = :name and channel = :channel", array(
            ':name' => $name,
            ':channel' => $channel,
        ));

        return intval($pv_vo->total);
    }

    public static function updatePv($name = '', $channel = ''){
        $module = G::$param['route']['m'];
        $controller = G::$param['route']['c'];
        $action = G::$param['route']['a'];

        $url = $module . '/' . $controller . '/' . $action;
        if($name == ''){
            $name = $module . '/' . $controller . '/' . $action;
        }
        $channel = $channel == '' ? 'wft_wechat' : $channel;

        $pv_vo = PageViewVo::model()->find("name = :name and channel = :channel", array(
            ':name' => $name,
            ':channel' => $channel,
        ));

        if($pv_vo){
            $total = $pv_vo->total + 1;
            $pv_vo->total = $total;
            $pv_vo->save();
        }else{
            $pv_vo = new PageViewVo();
            $pv_vo->name = $name;
            $pv_vo->url = $url;
            $pv_vo->channel = $channel;
            $pv_vo->total = 1;
            $pv_vo->created = TimeUtil::getNowDateTime();
            $pv_vo->updated = TimeUtil::getNowDateTime();

            $pv_vo->save();
        }

        $total = Yii::app()->db->createCommand("select sum(total) from ps_page_view where name = '{$name}'")->queryScalar();
        return $total;
    }
}