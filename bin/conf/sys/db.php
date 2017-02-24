<?php
return array (
	'offline1' => array (
		'host1' => '10.0.32.10',
		//'host' => '172.16.2.10',
		'port' => 3306,
		'name' => 'wft',
		'username' => 'root',
		'password' => '12345677',
		'charset' => 'utf8',),

	'offline2' => array (
		'host' => '127.0.0.1',//'172.16.2.10',
		'port' => 3306,
		'name' => 'direct_sale',
		'username' => 'root',
		'password' => 'sdcqa',
		'charset' => 'utf8',),

	'offline' => array (
		'host' => '106.14.83.41',
		'port' => 3306,
		'name' => 'wft',
		'username' => 'wft_dev',
		'password' => 'wanfantian',
		'charset' => 'utf8',),

	'online' => YII_PAY_DEV ? array (
		'host' => '127.0.0.1',
		'port' => 3306,
		'name' => 'wft',
		'username' => 'root',
		'password' => 'wanfantian',
		'charset' => 'utf8',) : array (
		'host' => '127.0.0.1',
		'port' => 3306,
		'name' => 'wft',
		'username' => 'root',
		'password' => 'wanfantian',
		'charset' => 'utf8',),
);