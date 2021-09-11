<?php
$wxpay_config = array (	
		//应用ID,您的APPID。
		// 'appid' => 'wxfe1d7a5c3326dc83',
		'appid' => 'wx721dd1fa0538e248',
		'mchid' => '1613946214',
		'secret' => 'bUidFSB00eufKPcq2mdFlHCP7igfQVVu',
		'key' => 'yMdwCnPS4yDXnMjJuBZB8YPTym71kUon',
		'order_api' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/h5',
		// 'serial_num' => '235D6DA1E25D6B48FCCB0A71239525A714C5215C',
		'serial_num' => '7CDEAF34F3277877439392335D475251324FD036',
		
		//异步通知地址
		'notify_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/Cash/wxpayNotify',
		
		
);