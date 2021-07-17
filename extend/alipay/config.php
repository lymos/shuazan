<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => '2021000117690345',

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => 'MIIEowIBAAKCAQEAnfdIV4Y3xcxVirs0lVs8dc4nEh+KwLk2EBrG8zP+DLv/B6PfnK9pbJPtqS/47768uUi8OQiAzmmUfMydhaZ8oKNX7e4q3xnvZLJ/aKi4Kacw6nBMp3O3JfY9qYbDCKScCg4yP0kVcvZNjHarZVp+HPEvwjbEmZGhcyPtMpuP/UPuNo5wAcwQ9xMaeDT5HwkhyRl9QCBUlai4eAsLHej/Wlz7dBR69kAwMbsyYq1CmwYcdK8lG5+OgWb8gCM5Hnk8Dbm5uXRemHTdlmFCCZqSc+F60noNWvV/QJZqM7LywfTbox+DAYaE9n7wjr/HIabHRgSUXgxda8ZXjd92UZgLhwIDAQABAoIBAHTIc1gZpzP5a6hj3G0rBVjGrwXsAcWXZ8uiMEFux7wcZF/+m/uXhyY5evOgvrZn2dhVWKoFikyPq7JUB4TeX7bW69PARzunTd+xQxZZ3cUVkMlReqo1l0pecJnbQmcqYx3f/u/glRXn188nkHEe5Kt69+bqXJB4JNcd0WefsGT43ZmnF3oXeIqXi9lUvtM/uQpDnE3Z0vey6iBe3xqw4S+dDpP11HISaZ+paY6ZCE4wWsVNPzPpzYX7JIjwa1OG2fVKFNegvuGSdBo+2K6Q7qk0JGBZl9cTChK5aSw3uYCEkMF0nStBtXGd8B5MJl2iETn4ZGo6idBbz6c3TZF9+fECgYEA65bPio5+1bmav66urUesyQbcQ0Bqln5IWBnzizsfQqXVMo5ASEWrBFtvoMka6z55ONZMNpDNYMuhNSozapZGL205sbt1QoOWfbNEV/NRFKvYo62pgfLeTRLqRvGWapN5G81AZl2qRagJ8EvfoHpQ36ffXr0t2xzPgza1G8uNCNkCgYEAq6bZPXW3c4rzHmqa8wnP0J6KBEWaFVlCaITOLjqSQlEG1de8MoVuevsnbVqOJYI9nkA/M2oP5nn77wlx7UBuHPVFe2JaD3e5hcXWF5vwpRi6Ypf2U8QNyPR6rXtEAgRRt1bSwrZep9T7puWfUmi2BAs2mJgI+/9k0I1MDIAd+18CgYALVnyyfF0aSA5sfymk6TgljJD5I5Z9m6I7qzQPVU8R1A1Im5P4ZnHuib/7ivIitCT9WULdtWZM7D/b4SYMUSMbkTQfm5fvFYoejowRd4JFbmfEqMzplhiguPJRa7sDcgl4Dw60XnmdVJL135DpljliKiAT3SLh0qch93YL2mX1MQKBgFSWnGeVIxplVvmeP02VgDumZQ1F33C10+v9xj0xz/GuTFgFQFEFQDSKoBtbjMEfNY5OytZjZgKGCoj5dpMcNSdAF0V+ajNFJL+vhpmL10MEtJTxngoZi1GEaRAktpbn2f0nZCVgCbVbV8gfUCwkPc4wWG9KPOMyMFQ1zUw7toqxAoGBALq/twddHSMxo7r7+IhzBgR7bgN/mB16t3f/3H3rcA9/N4p163h18YIaDHoqvnwzaNa4AwNKfjnFaDzpz5azWApzd5tXsPMoRBWGWv1lMGwf2Z0I9TAzZjQV2MFaesISsW1m44N5IgP4XUmANNGXZTysP61plQKMg+62I7AIDQtC',
		
		//异步通知地址
		'notify_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/index.php?s=Cash/alipayNotify',
		
		//同步跳转
		'return_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/index.php?s=Cash/alipayReturn',

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		// 'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxY2ldpSkBpWGmRvXKnOGu8RMFv7UIEgv+2kSBHP4PGRhJhc1kEDdBmLaWy9qVQssvweQmhTvjN6kgu60Xw8i4CeNtOei9X1zgfybDb2KqhLAg1iIYvanowdVoq+Tu4XbuLekyN517HQlKfocmVzmbh6p5ZhnGCs9c5Njan9NfU6hpqMapV1i5bLT43RzGLeZzO5X/ZmGv2kiUHQyixYtAgnX9RdHZ+eqtE9vrq1UqooDQ3rOKeWQSPfI5wfdesWJ9BkRd9horBYkOx/I3/f73eZRv/7DlGIkQL8yFw4oeFKgMln/bPV7syt9F3zdiAbZr26AfXVW/tyjnMSsMMLU0wIDAQAB',
		
	
);