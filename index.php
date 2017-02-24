<?php
//定义根目录:即 index.php 所在目录
define('DIR_ROOT', dirname(__FILE__) . '/');

$exceptUaReg = array(
    '/Alibaba/',
    '/majestic12/',
    '/^python-requests/',
    '/Apache-HttpClient/',
    '/^Mozilla\/5.0$/',
);
foreach($exceptUaReg as $reg){
    if (preg_match($reg, $_SERVER['HTTP_USER_AGENT'])) {
        exit;
    }
}

//应用程序环境
$ENV_NAME = 'online';
$ENV_NAME = 'offline';

// change the following paths if necessary
defined('YII_PAY_DEV') or define('YII_PAY_DEV', true);

if ($ENV_NAME == 'online') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    defined('YII_DEBUG') or define('YII_DEBUG', false);
    defined('YII_PAY_DEV') or define('YII_PAY_DEV', false);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
    // remove the following lines when in production mode
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    // specify how many levels of call stack should be shown in each log message
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
}

//yii 源码所在位置，如需升级框架，直接覆盖此文件夹的代码
$yii = DIR_ROOT . 'framework/yii/yii.php';

//初始化框架的配置文件
if(preg_match('/\/file\/showImage/', $_SERVER['REQUEST_URI'])){
    $config = DIR_ROOT . 'framework/config/main.php';
}

$config = DIR_ROOT . 'framework/config/main.php';

require_once($yii);
Yii::createWebApplication($config)->run();

