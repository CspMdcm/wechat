<?php
return [
	// appID
	'app_id' 	  => '',
	// appsecret
	'app_secret'  => '',
	// Token
	'token'		  => '',
	// 商户号
	'merch_id'    => '',
	// api密钥
	'api_key'	  => '',
	
	// 日志记录
	'log' => [
		// 存储路径
		'path' 		=> __DIR__ . '/Logs',
		// 最大存储大小
		'save_size' => 1024 * 1024,
        // 存储名称
        'save_name' => 'curr',
        // 保存默认后缀
        'save_ext'  => 'log',
        // 备份默认后缀
        'bak_ext'   => 'bak'
	]
];