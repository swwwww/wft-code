<?php
/**
 * 网站配置文件
 * @author        qintao <11942518@qq.com>
 * @copyright     Copyright (c) 2014-2015 . All rights reserved.
 */
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
Yii::setPathOfAlias('components', DIR_ROOT . 'framework/components/');

// This is the main Web application configuration.
// Any writable CWebApplication properties can be configured here.
return array(
	'basePath' => DIR_ROOT . '/bin',
	'name' => 'wanfantian',
	'language'=>'zh_cn',
	'theme'=>'default',
	'timeZone'=>'Asia/Shanghai',
    // preloading special component
	'preload' => array('initFramework', 'log', 'fatalerrorcatch',),//'fatalerrorcatch',

    // autoloading model and component classes
	'import' => array(
		//'application.components.sys.*',//公用组件
		'components.sys.*',
		'components.third.*',
        'components.third.MongoYii.*',
        'components.third.MongoYii.validators.*',
        'components.third.MongoYii.behaviors.*',
        'components.third.MongoYii.util.*',
		'application.models.*',//公用模型
		'application.filters.*',
		'application.managers.*',//公用业务
		'application.managers.lib.*',
		'application.managers.util.*',
		'application.managers.common.*',
		'application.managers.data.*',
		'application.managers.manage.*',
		'application.managers.service.*',
		'application.managers.resource.*',
    ),

    'modules' => array(
        'api' => array(),
		'demo' => array(),
		'admin' => array(),

    ),

	'defaultController' => 'recommend',

    // application components
	'components'=>array(
        'initFramework' => array(
            //ext 别名：extensions
            //'class' => 'ext.sys.Init',
            'class' => 'components.sys.InitFramework',
            'key' => 'test',
        ),
        'request' => array(
            'class' => 'components.sys.CsrfPassHttpRequest',
            'enableCsrfValidation' => true,
        ),
		'user' => array(
            // enable cookie-based authentication
			//'allowAutoLogin' => true,
        ),

		'urlManager' => array(
			'urlFormat' => 'path',
            'showScriptName' => false,
			'rules' => array(
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),

        'session' => array(
            'class' => 'CDbHttpSession',
            'connectionID' => 'db',
            'sessionTableName' => 'yii_ds_session',
            'autoCreateSessionTable' => false,
            'timeout' => 7 * 24 * 3600,
            'autoStart' => true,
            'sessionName' => 'wanfantian-uuid',
        	'cookieMode' => 'only',
        ),

        'session1' => array(
            'class' => 'CCacheHttpSession',
            'cacheID' => 'file_cache',
            'timeout' => 6 * 3600,
            'autoStart' => true,
            'sessionName' => 'wanfantian-uuid',
        	'cookieMode' => 'only',
        ),

        'fatalerrorcatch'=>array(
            'class'=>'components.sys.FatalErrorCatch',
            'errorAction'=>'site/sorry',
        ),

        'errorHandler' => array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/sorry',
		),

        'mongodb' => array(
            'class' => 'EMongoClient',
            'server' => 'mongodb://127.0.0.1:27017',
            'db' => 'wft'
        ),

		'file_cache' => array(
		    'class' => YII_DEBUG ? 'system.caching.CFileCache' : 'system.caching.CApcCache',
		),

		'cache' => YII_DEBUG ?
    		array(
                'class' => 'system.caching.CFileCache'
            ) : array(
                'class' =>  'CRedisCache',
                'hostname' => YII_DEBUG ? '172.16.2.10' : '127.0.0.1',
                'port' => 6379,
                'database' => 0,
                'hashKey' => false,
                'keyPrefix' => '',
            ),

		YII_DEBUG ? '' : 'log' =>   array(
			'class' => 'CLogRouter',
			'routes' => array(
                array(
					'class'=>'CFileLogRoute',
					'levels'=>'error',
					'logPath' => '/mnt/log/playsky/',
					'logFile' => 'error.log',
                    'maxFileSize' => 500 * 1024,
                    'maxLogFiles' => 6,
                ),
                array(
					'class'=>'CFileLogRoute',
					'levels'=>'info',
					'logPath' => '/mnt/log/playsky/',
					'logFile' => 'info_' . date('Ymd', time()) . '.log',
                    'maxFileSize' => 500 * 1024,
                    'maxLogFiles' => 10,
                ),
                array(
					'class'=>'CFileLogRoute',
					'levels'=>'trace',
					'logPath' => '/mnt/log/playsky/',
					'logFile' => 'trace_' . date('Ymd', time()) . '.log',
                    'maxFileSize' => 800 * 1024,
                    'maxLogFiles' => 15,
                ),
            ),
        ),
    ),

    // application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
    'params' => array(
        'domain' => 'play.wanfantian.com',
        'api_key' => '0Wo5TCP18L6Gxb1v',//p0fdDAT3XjLQHTPn
	    'wechat' => array(
            'appid' => YII_DEBUG ? 'wx8e4046c01bf8fff3' : 'wx8e4046c01bf8fff3',    // wx342746187426c82b
            'secret' => YII_DEBUG ? 'e1575add3e5cd7420f79526fbcab25ba' : '117363e098bfb12d20365ccee4599e2e',
            'token' => '',
            'PartnerKey' => '5IXUguLZ2L3NDH7Ud76nIWj6FQDOsG5A', //支付key
            'PartnerID' => '1315364201',
            'notify_url' => 'http://wan.wanfantian.com/web/notify/weixin'
        ),
	),
);