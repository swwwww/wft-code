<?php
/**
 * 通用资源 - 静态模式
 * @Description:
 * @ClassName: Resource
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2015-3-17 下午10:34:41
 */
class Resource {
    public static function instance($className=__CLASS__) {
        $instance = new $className;
        return $instance;
    }
}

