<?php
/**
 * 自动引入相关 lib 文件 帮助类
 * @Description:
 * @ClassName: ImportUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-21 上午10:31:02
 */
class ImportUtil extends Manager{

    /**
     * 根据指定的 模式，引入相应的lib文件。默认为base。
     * @Description: 有类型可新增，但别修改已有类型。
     * @Title: import
     * @param unknown_type $module
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-1-21 上午10:32:41
     */
    public static function import($module){
        //公共模块 的类已经在framework中全部引入

        //默认需要加载自身模块的类
        Yii::import("application.modules.{$module}.models.*");
        Yii::import("application.modules.{$module}.managers.lib.*");
        Yii::import("application.modules.{$module}.managers.util.*");
        Yii::import("application.modules.{$module}.managers.data.*");
        Yii::import("application.modules.{$module}.managers.service.*");

        //单独引入
        switch($mode){
            case 'main':
                break;
            case 'error':
                break;
            case 'user':
                break;
            case 'login':
                break;
            default:
                break;
        }

        return true;
    }
}