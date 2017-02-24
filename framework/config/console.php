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
	'timeZone'=>'Asia/Shanghai',
    // preloading special component
	'preload' => array('initFramework'),//'fatalerrorcatch',

    // autoloading model and component classes
	'import' => array(
		//'application.components.sys.*',//公用组件
		'components.sys.*',
		'components.third.*',
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

    // application components
	'components'=>array(
        'initFramework' => array(
            //ext 别名：extensions
            //'class' => 'ext.sys.Init',
            'class' => 'components.sys.InitFramework',
            'key' => 'test',
        ),

		'cache' => array(
		    'class' => YII_DEBUG ? 'system.caching.CFileCache' : 'system.caching.CApcCache',
		),

		/*'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
                array(
					'class'=>'CWebLogRoute',
                    //error, warning
					'levels'=>'error',
                ),
            ),
        ),*/
    ),

    // application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
    'params'=>array(
        'province' => '湖南',
        'city' => '长沙',
        'loc_type' => array(
            '0' => 'province',
            '1' => 'city',
            '2' => 'district',
            '3' => 'street',
        ),
        'SITE_ANNOUNCE_NEWS_ID_LIST' => array(1, 2, 3, 4, 21),
        'UPYUN_CONFIG' => array(
            'upload_url' => 'http://v0.api.upyun.com/',
            'content_secret' => '',
            'bucket_name' => YII_DEBUG ? 'debug' : 'online',
            'form_api_token' => YII_DEBUG ? '=' : '=',
            'file_domain' => YII_DEBUG ? 'http://debug.b0.upaiyun.com' : 'http://n.com',
        ),
	),
);