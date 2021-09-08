<?php
$config = array (	
		//应用ID,您的APPID。
		'appid' => 'wxfe1d7a5c3326dc83',
		'mchid' => '1613946214',
		'secret' => '',
		'key' => 'yMdwCnPS4yDXnMjJuBZB8YPTym71kUon',
		'order_api' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/h5',
		
		//异步通知地址
		'notify_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/Cash/wxpayNotify',
		
		
);