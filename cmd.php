<?php

define('YII_DEBUG', false);

define('DIR_ROOT', dirname(__FILE__).'/');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//yii 源码所在位置，如需升级框架，直接覆盖此文件夹的代码
$yii = DIR_ROOT . 'framework/yii/yii.php';

//初始化框架的配置文件
$config = DIR_ROOT . 'framework/config/console.php';

// include Yii bootstrap file
require_once($yii);

// create application instance and run
Yii::createConsoleApplication($config)->run();
