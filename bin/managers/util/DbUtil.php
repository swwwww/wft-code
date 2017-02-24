<?php
/**
 * 数据库 操作 帮助类
 * @Description:
 * @ClassName: DbUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-21 上午10:31:02
 */
class DbUtil extends Manager{
    public static function setDb(){
        $db_name_arr = array(
            'db',
        	'session_db',
        );

        $db = array();
        foreach($db_name_arr as $key => $val){
            $db_name = $val;
            $db_conf_path = DIR_CONF . "sys/{$db_name}.php";

            $db_conf = require_once($db_conf_path);

            $db_component = self::getDb($db_conf);
            Yii::app()->setComponent($db_name, $db_component);
            /**
             * 2015-03-22: fixed ： 将Init.php文件名改为InitFramework后即可
             * 分析：可能是因为存在同名的init组件导致了Init被执行了两次
            //必须手动再获取一下，否则数据库初始化失败 - WTF
            Yii::app()->getDb($db_name);
             */
        }

        return true;
    }

    public static function getDb($db_config){
        if(YII_DEBUG === true && isset($db_config['offline'])){
            $db_info = $db_config['offline'];
        }else{
            $db_info = $db_config['online'];
        }

        $db = array(
            'class' => 'CDbConnection' ,
            'connectionString'=> 'mysql:host=' . $db_info['host'] . ';dbname=' . $db_info['name'] . ';port=' . $db_info['port'],
            'username' => $db_info['username'],
            'password' => $db_info['password'],
            'charset' => $db_info['charset']
        );

        return $db;
    }

    
}