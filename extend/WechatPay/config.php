<?php
$config = array (	
		//应用ID,您的APPID。
		'appid' => '2021000117690345',
		'mchid' => '',
		'secret' => '',
		'key' => '',
		'order_api' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/h5',
		
		//异步通知地址
		'notify_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/index.php?s=Cash/wxpayNotify',
		
		
);