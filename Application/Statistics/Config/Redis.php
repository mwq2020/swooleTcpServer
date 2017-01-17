<?php
/**
 * redis 配置文件
 * @author xmc
 */

namespace Config;
class Redis
{
	//redis默认配置
	public $default = array(
		'nodes' => array(
			array('master' => "127.0.0.1:6379", 'slave' => "127.0.0.1:6379"),
		),
		'db' => 0
	);

}

