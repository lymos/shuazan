<?php
/**
 * 金钱相关
 */
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Log;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Formatter;
use WeChatPay\Util\PemUtil;
use WeChatPay\Builder;
 
class Cash extends BaseController
{
	public function __construct(\think\App $app)
	{
		parent::__construct($app, false);
	}
	
	/**
	 * 付款
	 */
	public function unionpay(){
		$merid = Config::get('app.unionpay_merid');
		$host = Config::get('app.host');
		
		$userid = intval($this->decrypt(str_replace(' ', '+', Request::param('userid'))));
		$order_str = str_replace(' ', '+', Request::param('orderid'));
		$orderid = $this->decrypt($order_str);
		// 查出订单信息
		$order = $this->_getOrder($userid, $orderid);
		if(! $order || ! isset($order['total'])){
			echo '订单不存在';
			exit;
		}
		$total = $order['total'];
		$product_name = $order['name'];
		$time = date('YmdHis');
		$amt = $total * 100; // 分
		$params = array(
				
				//以下信息非特殊情况不需要改动
				'version' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,                 //版本号
				'encoding' => 'utf-8',				  //编码方式
				'txnType' => '01',				      //交易类型
				'txnSubType' => '01',				  //交易子类
				'bizType' => '000201',				  //业务类型
				'frontUrl' =>  $host . 'index.php?s=Cash/frontReceive',  //前台通知地址
				'backUrl' => $host . 'index.php?s=cash/backReceive',	   //后台通知地址
				'signMethod' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,	              //签名方法
				'channelType' => '08',	              //渠道类型，07-PC，08-手机
				'accessType' => '0',		          //接入类型
				'currencyCode' => '156',	          //交易币种，境内商户固定156
				
				//TODO 以下信息需要填写
				'merId' => $merid,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
				'orderId' => $orderid,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
				'txnTime' => $time,	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
				'txnAmt' => $amt,	//交易金额，单位分，此处默认取demo演示页面传递的参数
				
				// 订单超时时间。
				// 超过此时间后，除网银交易外，其他交易银联系统会拒绝受理，提示超时。 跳转银行网银交易如果超时后交易成功，会自动退款，大约5个工作日金额返还到持卡人账户。
				// 此时间建议取支付时的北京时间加15分钟。
				// 超过超时时间调查询接口应答origRespCode不是A6或者00的就可以判断为失败。
				'payTimeout' => date('YmdHis', strtotime('+15 minutes')), 
		
				'riskRateInfo' =>'{commodityName=' . $product_name . '}',
		
				// 请求方保留域，
				// 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
				// 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
				// 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
				//    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
				// 2. 内容可能出现&={}[]"'符号时：
				// 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
				// 2) 如果对账文件没有显示要求，可做一下base64（如下）。
				//    注意控制数据长度，实际传输的数据长度不能超过1024位。
				//    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
				//    'reqReserved' => base64_encode('任意格式的信息都可以'),
				
				//TODO 其他特殊用法请查看 special_use_purchase.php
			);
		
		\com\unionpay\acp\sdk\AcpService::sign ( $params );
		$uri = \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->frontTransUrl;
		$html_form = \com\unionpay\acp\sdk\AcpService::createAutoFormHtml( $params, $uri );
		echo $html_form;
		exit;
	}
	
	/**
	 * 付款
	 */
	public function unionpayApp(){
		$merid = Config::get('app.unionpay_merid');
		$host = Config::get('app.host');
		$userid = intval($this->decrypt(str_replace(' ', '+', Request::param('userid'))));
		$order_str = str_replace(' ', '+', Request::param('orderid'));
		$orderid = $this->decrypt($order_str);
		// 查出订单信息
		$order = $this->_getOrder($userid, $orderid);
		if(! $order || ! isset($order['total'])){
			echo '订单不存在';
			exit;
		}
		$total = $order['total'];
		$product_name = $order['name'];
		$time = date('YmdHis');
		$amt = $total * 100; // 分
		$params = array(
				
				//以下信息非特殊情况不需要改动
				'version' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,                 //版本号
				'encoding' => 'utf-8',				  //编码方式
				'txnType' => '01',				      //交易类型
				'txnSubType' => '01',				  //交易子类
				'bizType' => '000201',				  //业务类型
				'frontUrl' =>  $host . 'Cash/frontReceiveApp',  //前台通知地址
				'backUrl' => $host . 'cash/backReceive',	  //后台通知地址
				'signMethod' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,	              //签名方法
				'channelType' => '08',	              //渠道类型，07-PC，08-手机
				'accessType' => '0',		          //接入类型
				'currencyCode' => '156',	          //交易币种，境内商户固定156
				
				//TODO 以下信息需要填写
				'merId' => $merid,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
				'orderId' => $orderid,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
				'txnTime' => $time,	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
				'txnAmt' => $amt,	//交易金额，单位分，此处默认取demo演示页面传递的参数
				
				// 订单超时时间。
				// 超过此时间后，除网银交易外，其他交易银联系统会拒绝受理，提示超时。 跳转银行网银交易如果超时后交易成功，会自动退款，大约5个工作日金额返还到持卡人账户。
				// 此时间建议取支付时的北京时间加15分钟。
				// 超过超时时间调查询接口应答origRespCode不是A6或者00的就可以判断为失败。
				'payTimeout' => date('YmdHis', strtotime('+15 minutes')), 
		
				'riskRateInfo' =>'{commodityName=' . $product_name . '}',
		
				// 请求方保留域，
				// 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
				// 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
				// 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
				//    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
				// 2. 内容可能出现&={}[]"'符号时：
				// 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
				// 2) 如果对账文件没有显示要求，可做一下base64（如下）。
				//    注意控制数据长度，实际传输的数据长度不能超过1024位。
				//    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
				//    'reqReserved' => base64_encode('任意格式的信息都可以'),
				
				//TODO 其他特殊用法请查看 special_use_purchase.php
			);
		
		\com\unionpay\acp\sdk\AcpService::sign ( $params );
		$uri = \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->frontTransUrl;
		$html_form = \com\unionpay\acp\sdk\AcpService::createAutoFormHtml( $params, $uri );
		echo $html_form;
		exit;
	}
	
	private function _getOrder($userid, $orderid){
		$where = [
			'a.orderid' => $orderid,
			'a.userid' => $userid,
			'a.status' => 0
		];
		$order = Db::name('order')
			->alias('a')
			->leftJoin('order_detail b', 'a.id = b.orderid')
			->leftJoin('product c', 'b.productid = c.id')
			->field('a.total, c.name')
			->where($where)->find();
		return $order;
	}
	
	public function alipayReturn(){
		
		$alipay_path = BASE_PATH . '/extend/alipay/';
		require $alipay_path . 'config.php';
		require_once $alipay_path . 'wappay/service/AlipayTradeService.php';
		
		$arr = $_GET;
		Log::write('alipay return in ' . $arr['out_trade_no'], 'alipay-return');
		
		$alipaySevice = new \AlipayTradeService($config); 
		$result = $alipaySevice->check($arr);
		
		/* 实际验证过程建议商户添加以下校验。
		1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
		2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
		3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
		4、验证app_id是否为该商户本身。
		*/
		if($result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代码
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

			$app_id = Request::param('app_id');
			$orderId = Request::param('out_trade_no');
			$trade_status = Request::param('trade_status');
			$total_amount = Request::param('total_amount');
			Log::write('alipay return orderid: ' . $orderId . ' in', 'alipay-return');
			
			
			if($config['app_id'] != $app_id){
				Log::write('alipay return orderid: ' . $orderId . ' app_id error ' . $app_id, 'alipay-return');
				$this->payBack();
			}
			
			if($trade_status != 'TRADE_SUCCESS'){
				// log
				Log::write('alipay return orderid: ' . $orderId . ' trade failed status: ' . $trade_status, 'alipay-return');
				$this->payBack();
			}
			Log::write('alipay return orderid: ' . $orderId . ' trade status: ' . $trade_status, 'alipay-return');
			
			$date = date('Y-m-d H:i:s');
			// 修改订单状态
			$update_data = [
				'status' => 1,
				'pay_type' => 1,
				'updated_date' => $date
			];
			$update_where = [
				'orderid' => $orderId,
				'status' => 0,
				'total' => str_replace('.00', '', $arr['total_amount'])
			];
			
			// 更新订单付款状态
			$update_status = Db::name('order')
				->where($update_where)
				->update($update_data);
			if(! $update_status){
				Log::write('alipay return orderid: ' . $orderId . ' update sql error ' . Db::getLastSql(), 'alipay-return');
				$this->payBack();
			}
		    
			$this->payBack(1);
		
			/*
			//商户订单号
		
			$out_trade_no = htmlspecialchars($arr['out_trade_no']);
		
			//支付宝交易号
		
			$trade_no = htmlspecialchars($arr['trade_no']);
				
			echo "验证成功<br />外部订单号：".$out_trade_no;
		*/
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		else {
			Log::write('alipay return verify fail ' . $arr['out_trade_no'], 'alipay-return');
		    //验证失败
		    $this->payBack();
		}
	}
	
	public function payBack($status = 0){
		$url = $this->url('/index.php?s=home');
		// header('Location: ' . $url);
		if($status){
			$txt = '付款成功';
		}else{
			$txt = '付款失败';
		}
		echo $txt . ' <a href="' . $url . '">首页</a>';
		exit;
	}
	
	public function alipayNotify(){
		
		$alipay_path = BASE_PATH . '/extend/alipay/';
		require $alipay_path . 'config.php';
		require_once $alipay_path . 'wappay/service/AlipayTradeService.php';

		$arr = $_POST;
		Log::write('alipay notify in ' . $arr['out_trade_no'], 'alipay-notify');
		$alipaySevice = new \AlipayTradeService($config); 
		$alipaySevice->writeLog(var_export($_POST,true));
		$result = $alipaySevice->check($arr);

		/* 实际验证过程建议商户添加以下校验。
		1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
		2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
		3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
		4、验证app_id是否为该商户本身。
		*/
		if($result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代
		
			
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
			
		    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			
			$app_id = Request::param('app_id');
			$orderId = Request::param('out_trade_no');
			$trade_status = Request::param('trade_status');
			$total_amount = Request::param('total_amount');
			Log::write('alipay notify orderid: ' . $orderId . ' in', 'alipay-notify');


			if($config['app_id'] != $app_id){
				Log::write('alipay notify orderid: ' . $orderId . ' app_id error ' . $app_id, 'alipay-notify');
				echo 'fail';
				exit;
			}
			
			if($trade_status != 'TRADE_SUCCESS'){
				// log
				Log::write('alipay notify orderid: ' . $orderId . ' trade failed status: ' . $trade_status, 'alipay-notify');
				echo 'fail';
				exit;
			}
			Log::write('alipay notify orderid: ' . $orderId . ' trade status: ' . $trade_status, 'alipay-notify');

			$date = date('Y-m-d H:i:s');
			// 修改订单状态
			$update_data = [
				'status' => 1,
				'pay_type' => 1,
				'updated_date' => $date
			];
			$update_where = [
				'orderid' => $orderId,
				'status' => 0,
				'total' => str_replace('.00', '', $arr['total_amount'])
			];

			// 更新订单付款状态
			$update_status = Db::name('order')
				->where($update_where)
				->update($update_data);
			if(! $update_status){
				Log::write('alipay notify orderid: ' . $orderId . ' update sql error ' . Db::getLastSql(), 'alipay-notify');
				echo 'fail';
				exit;
			}
			echo 'success';
			exit;
			
			/*
			//支付宝交易号
			$trade_no = $_POST['trade_no'];
		
			//交易状态
			$trade_status = $_POST['trade_status'];
		    if($_POST['trade_status'] == 'TRADE_FINISHED') {
		
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
					//如果有做过处理，不执行商户的业务程序
						
				//注意：
				//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
		    }
		    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//请务必判断请求时的total_amount与通知时获取的total_fee为一致的
					//如果有做过处理，不执行商户的业务程序			
				//注意：
				//付款完成后，支付宝系统发送该交易状态通知
		    }
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		        
			echo 'success';		//请不要修改或删除
			*/
		}else {
			Log::write('alipay notify verify fail ' . $arr['out_trade_no'], 'alipay-notify');
		    //验证失败
		    echo 'fail';	//请不要修改或删除
		
		}
		
	}

	public function wxpayNotify(){
		error_log(print_r('wxpayNotify', true) . "\r\n", 3, '/tmp/lymos-debug.log');
		error_log(print_r($_SERVER, true) . "\r\n", 3, '/tmp/lymos-debug.log');
		error_log(print_r($_POST, true) . "\r\n", 3, '/tmp/lymos-debug.log');
		error_log(print_r($_GET, true) . "\r\n", 3, '/tmp/lymos-debug.log');
		error_log(print_r($_REQUEST, true) . "\r\n", 3, '/tmp/lymos-debug.log');
		$xml = file_get_contents('php://input');
		if(! $xml){
			Log::write('wxpay notify xml empty', 'wxpay-notify');
		}
		$arr = json_decode($xml, true);
		$ser = $_SERVER;
		error_log(print_r($arr, true) . "\r\n", 3, '/tmp/lymos-debug.log');
		Log::write('wxpay notify in ' . $arr['id'], 'wxpay-notify');
		
		$wxpay_path = BASE_PATH . '/extend/WechatPay/';
		$cert_path = $wxpay_path . 'cert2/';
		require $wxpay_path . 'config.php';

		// Log::write('wxpay notify in ' . $arr['out_trade_no'], 'wxpay-notify');
		
		$inWechatpaySignature = $ser['HTTP_WECHATPAY_SIGNATURE'];// 请根据实际情况获取
		$inWechatpayTimestamp = $ser['HTTP_WECHATPAY_TIMESTAMP'];// 请根据实际情况获取
		$inWechatpaySerial = $ser['HTTP_WECHATPAY_SERIAL'];// 请根据实际情况获取
		$inWechatpayNonce = $ser['HTTP_WECHATPAY_NONCE'];// 请根据实际情况获取
		$inBody = $xml;// 请根据实际情况获取，例如: file_get_contents('php://input');
		
		$apiv3Key = $wxpay_config['secret'];// 在商户平台上设置的APIv3密钥
		
		// 根据通知的平台证书序列号，查询本地平台证书文件，
		// 假定为 `/path/to/wechatpay/inWechatpaySerial.pem`
		$platformPublicKeyInstance = Rsa::from('file://' . $cert_path . 'wechatpay_platform_cert.pem', Rsa::KEY_TYPE_PUBLIC);
		
		// 检查通知时间偏移量，允许5分钟之内的偏移
		$timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
		$verifiedStatus = Rsa::verify(
		    // 构造验签名串
		    Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, $inBody),
		    $inWechatpaySignature,
		    $platformPublicKeyInstance
		);
		if ($timeOffsetStatus && $verifiedStatus) {
		    $inBodyArray = (array)json_decode($inBody, true);
		    ['resource' => [
		        'ciphertext'      => $ciphertext,
		        'nonce'           => $nonce,
		        'associated_data' => $aad
		    ]] = $inBodyArray;
		    $inBodyResource = AesGcm::decrypt($ciphertext, $apiv3Key, $nonce, $aad);
		    $inBodyResourceArray = (array)json_decode($inBodyResource, true);
			error_log(print_r($inBodyResourceArray, true) . "\r\n", 3, '/tmp/lymos-debug.log');
		    // print_r($inBodyResourceArray);// 打印解密后的结果

		    $app_id = $inBodyResourceArray['appid'];
		    $orderId = $inBodyResourceArray['out_trade_no'];
			$trade_status = $inBodyResourceArray['trade_state'];
			$total_amount = $inBodyResourceArray['amount']['total'];
			Log::write('wxpay notify orderid: ' . $orderId . ' in', 'wxpay-notify');


			if($wxpay_config['appid'] != $app_id && $wxpay_config['app_appid'] != $app_id){
				Log::write('wxpay notify orderid: ' . $orderId . ' app_id error ' . $app_id, 'wxpay-notify');
				return json(['error' => true], 500);
			}
			
			if($trade_status != 'SUCCESS'){
				// log
				Log::write('wxpay notify orderid: ' . $orderId . ' trade failed status: ' . $trade_status, 'wxpay-notify');
				return json(['error' => true], 500);
			}
			Log::write('wxpay notify orderid: ' . $orderId . ' trade status: ' . $trade_status, 'wxpay-notify');

			$date = date('Y-m-d H:i:s');
			// 修改订单状态
			$update_data = [
				'status' => 1,
				'pay_type' => 2,
				'updated_date' => $date
			];
			$update_where = [
				'orderid' => $orderId,
				'status' => 0,
				'total' => $total_amount / 100
			];

			// 更新订单付款状态
			$update_status = Db::name('order')
				->where($update_where)
				->update($update_data);
			if(! $update_status){
				Log::write('wxpay notify orderid: ' . $orderId . ' update sql error ' . Db::getLastSql(), 'wxpay-notify');
				return json(['error' => true], 500);
			}
			// header('HTTP/1.1 200');
			return json(['error' => false], 200);
		}else{
			Log::write('wxpay notify verify fail ' . $arr['id'], 'wxpay-notify');
		    //验证失败
		   // header('HTTP/1.1 500');
			return json(['error' => true], 500);
		}
	}
	
	public function wxpay(){
		$wxpay_path = BASE_PATH . '/extend/WechatPay/';
		$cert_path = $wxpay_path . 'cert2/';
		require $wxpay_path . 'config.php';

		$userid = intval($this->decrypt(str_replace(' ', '+', Request::param('userid'))));
		$order_str = str_replace(' ', '+', Request::param('orderid'));
		$orderid = $this->decrypt($order_str);
		// 查出订单信息
		$order = $this->_getOrder($userid, $orderid);
		if(! $order || ! isset($order['total'])){
			echo '订单不存在';
			exit;
		}
		
		/*
		// 工厂方法构造一个实例
		$instance = \WeChatPay\Builder::factory([
		    // 商户号
		    'mchid' => $config['mchid'],
		    // 商户证书序列号
		    'serial' => 'XXXXXXXXXX',
		    // 商户API私钥 PEM格式的文本字符串或者文件resource
		    'privateKey' => \WeChatPay\Util\PemUtil::loadPrivateKey('/path/to/mch/apiclient_key.pem'),
		    'certs' => [
		        // 可由内置的平台证书下载器 `./bin/CertificateDownloader.php` 生成
		        'YYYYYYYYYY' => \WeChatPay\Util\PemUtil::loadCertificate('/path/to/wechatpay/cert.pem')
		    ],
		    // APIv2密钥(32字节)--不使用APIv2可选
		    'secret' => 'ZZZZZZZZZZ',
		    'merchant' => [// --不使用APIv2可选
		        // 商户证书 文件路径 --不使用APIv2可选
		        'cert' => '/path/to/mch/apiclient_cert.pem',
		        // 商户API私钥 文件路径 --不使用APIv2可选
		        'key' => '/path/to/mch/apiclient_key.pem',
		    ],
		]);
		*/

		// 商户号，假定为`1000100`
		$merchantId = $wxpay_config['mchid'];
		// 商户私钥，文件路径假定为 `/path/to/merchant/apiclient_key.pem`
		$merchantPrivateKeyFilePath = 'file://' . $cert_path . 'apiclient_key.pem';// 注意 `file://` 开头协议不能少
		// 加载商户私钥
		$merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
		$merchantCertificateSerial = $wxpay_config['serial_num'];// API证书不重置，商户证书序列号就是个常量
		// $merchantCertificateSerial = '';
		// // 也可以使用openssl命令行获取证书序列号
		// // openssl x509 -in /path/to/merchant/apiclient_cert.pem -noout -serial | awk -F= '{print $2}'
		// // 或者从以下代码也可以直接加载
		// // 「商户证书」，文件路径假定为 `/path/to/merchant/apiclient_cert.pem`
		// $merchantCertificateFilePath = 'file:///path/to/merchant/apiclient_cert.pem';// 注意 `file://` 开头协议不能少
		// // 解析「商户证书」序列号
		// $merchantCertificateSerial = PemUtil::parseCertificateSerialNo($merchantCertificateFilePath);
		
		// 「平台证书」，可由下载器 `./bin/CertificateDownloader.php` 生成并假定保存为 `/path/to/wechatpay/cert.pem`
		$platformCertificateFilePath = 'file://' . $cert_path . 'wechatpay_platform_cert.pem';// 注意 `file://` 开头协议不能少
		// 加载「平台证书」公钥
		$platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
		// 解析「平台证书」序列号，「平台证书」当前五年一换，缓存后就是个常量
		$platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
		
		// 工厂方法构造一个实例
		$instance = Builder::factory([
		    'mchid'      => $merchantId,
		    'serial'     => $merchantCertificateSerial,
		    'privateKey' => $merchantPrivateKeyInstance,
		    'certs'      => [
		        $platformCertificateSerial => $platformPublicKeyInstance,
				// '68AECFD1DA78E4A8FAF6F6CC26C5F3178BA4D8BA' => $platformPublicKeyInstance,
		    ],
		    // APIv2密钥(32字节)--不使用APIv2可选
		    // 'secret' => 'exposed_your_key_here_have_risks',// 值为占位符，如需使用APIv2请替换为实际值
		    // 'merchant' => [// --不使用APIv2可选
		    //     // 商户证书 文件路径 --不使用APIv2可选
		    //     'cert' => $merchantCertificateFilePath,
		    //     // 商户API私钥 文件路径 --不使用APIv2可选
		    //     'key' => $merchantPrivateKeyFilePath,
		    // ],
		]);
		
		try {
			$total = floatval($order['total']) * 100;
			// $total = intval(1); // debug
			$data = [
		        'mchid'        => $merchantId,
		        'out_trade_no' => $orderid,
		        'appid'        => $wxpay_config['appid'],
		        'description'  => $order['name'],
		        'notify_url'   => $wxpay_config['notify_url'],
		        'amount'       => [
		            'total'    => $total,
		            'currency' => 'CNY'
		        ],
				'scene_info' => [
					'payer_client_ip' => $this->getIp(),
					'h5_info' => [
						'type' => 'Wap'
					]
				]
		    ];
			
			// $instance->v3->pay->transactions->h5->post
			// $resp = $instance->v3->pay->transactions->native
		  // $resp = $instance->chain('v3/pay/transactions/h5')
			$resp = $instance->v3->pay->transactions->h5
		    ->post(['json' => $data]);
		    $res = $resp->getBody()->getContents();
			if(strpos($res, 'h5_url') === false){
				$ret['msg'] = '下单失败';
				return json($ret);
			}
			$urls = json_decode($res, true);
			header('Location: ' . $urls['h5_url'] . '&redirect_url=' . urlencode($this->url('/Home/shop')));
			exit;
		// error_log(print_r($resp->getBody(), true) . "\r\n", 3, '/Volumes/mac-disk/work/www/debug.log');
		
			//echo $resp->getStatusCode() . ' ' . $resp->getReasonPhrase(), PHP_EOL;
		   // echo $resp->getBody(), PHP_EOL;
		} catch (Exception $e) {
			/*
			$msg = '付款失败 code wx_00002';
			if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
			    $r = $e->getResponse();
			    $msg = 'error: ' . $r->getStatusCode() . ' ' . $r->getReasonPhrase() . ' ' . $r->getBody();
			}
			$ret['msg'] = $msg;
			return json($ret);
			*/
		    // 进行错误处理
		    
		    echo $e->getMessage(), PHP_EOL;
		    if ($e instanceof \Psr\Http\Message\ResponseInterface && $e->hasResponse()) {
		        echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase(), PHP_EOL;
		        echo $e->getResponse()->getBody();
		    }
		    
		}

	}
	
	public function alipay(){
		$alipay_path = BASE_PATH . '/extend/alipay/';
		require_once $alipay_path . 'wappay/service/AlipayTradeService.php';
		require_once $alipay_path . 'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
		require $alipay_path . 'config.php';
		
		$userid = intval($this->decrypt(str_replace(' ', '+', Request::param('userid'))));
		$order_str = str_replace(' ', '+', Request::param('orderid'));
		$orderid = $this->decrypt($order_str);
		// 查出订单信息
		$order = $this->_getOrder($userid, $orderid);
		if(! $order || ! isset($order['total'])){
			echo '订单不存在';
			exit;
		}
		$total = $order['total'];
		$product_name = $order['name'];
		$time = date('YmdHis');
		$amt = $total;
		
		if (!empty($orderid)&& trim($orderid)!=""){
		    //商户订单号，商户网站订单系统中唯一订单号，必填
		    $out_trade_no = $orderid;
		
		    //订单名称，必填
		    $subject = $product_name;
		
		    //付款金额，必填
		    $total_amount = $amt;
		
		    //商品描述，可空
		    $body = '';
		
		    //超时时间
		    $timeout_express = "1m";
		
		    $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
		    $payRequestBuilder->setBody($body);
		    $payRequestBuilder->setSubject($subject);
		    $payRequestBuilder->setOutTradeNo($out_trade_no);
		    $payRequestBuilder->setTotalAmount($total_amount);
		    $payRequestBuilder->setTimeExpress($timeout_express);
		    $payResponse = new \AlipayTradeService($config);
			
		    $result = $payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
			return ;
		}
		
		// View::assign('invite_code', $invite_code);
		return View::fetch('alipay');
	}

	public function aliOrder(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$alipay_path = BASE_PATH . '/extend/alipay-sdk/';
		require_once $alipay_path . 'aop/AopClient.php';
		require_once $alipay_path . 'aop/AopCertification.php';
		require_once $alipay_path . 'aop/request/AlipayTradeQueryRequest.php';
		require_once $alipay_path . 'aop/request/AlipayTradeWapPayRequest.php';
		require_once $alipay_path . 'aop/request/AlipayTradeAppPayRequest.php';
		
		require $alipay_path . 'config.php';
		$order_obj = new Order($this->app);
		$order = $order_obj->createOrder(true);
		
		if(! $order || ! (is_array($order)) || ! isset($order['orderid'])){
			$ret['msg'] = '创建订单失败';
			return json($ret);
		}

		$aop = new \AopClient();
  		$aop->gatewayUrl = $config['gatewayUrl'];
		$aop->appId = $config['app_id'];
		$aop->rsaPrivateKey = $config['merchant_private_key'];
		$aop->format = 'json';
		$aop->charset = $config['charset'];
		$aop->postCharset = 'utf-8';
		$aop->apiVersion = '1.0';
		$aop->signType = $config['sign_type'];;
		$aop->alipayrsaPublicKey = $config['alipay_public_key'];
		//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		$request = new \AlipayTradeAppPayRequest();
		//SDK已经封装掉了公共参数，这里只需要传入业务参数

		$bizcontent = json_encode([
		                 //  'body'=>'**',
		                   'subject'=> $order['name'],
		                   'out_trade_no'=> $order['orderid'],
		                   'total_amount'=> $order['total'],
		                   // 'product_code'=>'QUICK_MSECURITY_PAY'
		                  ]);
		$request->setBizContent($bizcontent);
		$request->setNotifyUrl($config['notify_url']);
		$response = $aop->sdkExecute($request);
		$ret['data'] = $response;
		$ret['code'] = 1;
		return json($ret);
	}

	public function aliOrder3(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		
		$alipay_path = BASE_PATH . '/extend/alipay/';
		require_once $alipay_path . 'aop/AopClient.php';
		require_once $alipay_path . 'aop/request/AlipayTradeAppPayRequest.php';
		require $alipay_path . 'config.php';
		
		$order_obj = new Order($this->app);
		$order = $order_obj->createOrder(true);
		
		if(! $order || ! (is_array($order)) || ! isset($order['orderid'])){
			$ret['msg'] = '创建订单失败';
			return json($ret);
		}

		$aop = new \AopClient();
  		$aop->gatewayUrl = $config['gatewayUrl'];
		  $aop->appId = $config['app_id'];
		  $aop->rsaPrivateKey = $config['merchant_private_key'];
		  $aop->format = 'json';
		  $aop->charset = $config['charset'];
		  $aop->signType = $config['sign_type'];;
		  $aop->alipayrsaPublicKey = $config['alipay_public_key'];
		 //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
		  $request = new \AlipayTradeAppPayRequest();
		 //SDK已经封装掉了公共参数，这里只需要传入业务参数
		   
		  $bizcontent = json_encode([
		   'body'=>'**',
		   'subject'=> $order['name'],
		   'out_trade_no'=> $order['orderid'],//此订单号为商户唯一订单号
		   'total_amount'=> $order['total'],//保留两位小数
		   'product_code'=>'QUICK_MSECURITY_PAY'
		  ]);
		  $request->setNotifyUrl($config['notify_url']);
		  $request->setBizContent($bizcontent);
		 //这里和普通的接口调用不同，使用的是sdkExecute
		  $response = $aop->sdkExecute($request);
		 //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
		
		$ret['data'] = $response;
		$ret['code'] = 1;
		return json($ret);
	}
	
	public function aliOrder2(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		
		$alipay_path = BASE_PATH . '/extend/alipay/';
		require_once $alipay_path . 'aop/AopClient.php';
		require $alipay_path . 'config.php';
		
		$order_obj = new Order($this->app);
		$order = $order_obj->createOrder(true);
		
		if(! $order || ! (is_array($order)) || ! isset($order['orderid'])){
			$ret['msg'] = '创建订单失败';
			return json($ret);
		}
		
		$order_info = [
			'app_id' => $config['app_id'],
			'method' => 'alipay.trade.app.pay',
			'format' => 'JSON',
			'charset' => $config['charset'],
			'sign_type' => $config['sign_type'],
			'version' => "1.0",
			'return_url' => $config['return_url'],
			'notify_url' => $config['notify_url'],
			'timestamp' => date('Y-m-d H:i:s'),
			// 'sign' => $sign,
			'biz_content' => json_encode([
				'subject' => $order['name'],
				'out_trade_no' => $order['orderid'],
				'total_amount' => $order['total'],
				// 'product_code' =>  'QUICK_WAP_PAY',
				// quit_url: 'https://imgbed.cn/static/alipay-return.html'
			])
		];
		$aop = new \AopClient();
		$aop->rsaPrivateKey = $config['merchant_private_key'];
		$order_info['sign']	= $aop->rsaSign($order_info, 'RSA2');
		$order_info['biz_content'] = json_decode($order_info['biz_content'], true);
		$ret['data'] = $order_info;
		$ret['code'] = 1;
		return json($ret);
	}
	
	public function wxOrder(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		
		$data = [];
		
		$wxpay_path = BASE_PATH . '/extend/WechatPay/';
		$cert_path = $wxpay_path . 'cert2/';
		require $wxpay_path . 'config.php';
		
		$os_type = Request::param('os_type');
		
		$order_obj = new Order($this->app);
		$order = $order_obj->createOrder(true);
		
		if(! $order || ! (is_array($order)) || ! isset($order['orderid'])){
			$ret['msg'] = '创建订单失败';
			return json($ret);
		}
		
		// 商户号，假定为`1000100`
		$merchantId = $wxpay_config['mchid'];
		// 商户私钥，文件路径假定为 `/path/to/merchant/apiclient_key.pem`
		$merchantPrivateKeyFilePath = 'file://' . $cert_path . 'apiclient_key.pem';// 注意 `file://` 开头协议不能少
		// 加载商户私钥
		$merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
		$merchantCertificateSerial = $wxpay_config['serial_num'];// API证书不重置，商户证书序列号就是个常量
		// // 也可以使用openssl命令行获取证书序列号
		// // openssl x509 -in /path/to/merchant/apiclient_cert.pem -noout -serial | awk -F= '{print $2}'
		// // 或者从以下代码也可以直接加载
		// // 「商户证书」，文件路径假定为 `/path/to/merchant/apiclient_cert.pem`
		// $merchantCertificateFilePath = 'file:///path/to/merchant/apiclient_cert.pem';// 注意 `file://` 开头协议不能少
		// // 解析「商户证书」序列号
		// $merchantCertificateSerial = PemUtil::parseCertificateSerialNo($merchantCertificateFilePath);
		
		// 「平台证书」，可由下载器 `./bin/CertificateDownloader.php` 生成并假定保存为 `/path/to/wechatpay/cert.pem`
		$platformCertificateFilePath = 'file://' . $cert_path . 'wechatpay_platform_cert.pem';// 注意 `file://` 开头协议不能少
		// 加载「平台证书」公钥
		$platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
		// 解析「平台证书」序列号，「平台证书」当前五年一换，缓存后就是个常量
		$platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
		
		// 工厂方法构造一个实例
		$instance = Builder::factory([
		    'mchid'      => $merchantId,
		    'serial'     => $merchantCertificateSerial,
		    'privateKey' => $merchantPrivateKeyInstance,
		    'certs'      => [
		        $platformCertificateSerial => $platformPublicKeyInstance,
		    ],
		    // APIv2密钥(32字节)--不使用APIv2可选
		    // 'secret' => 'exposed_your_key_here_have_risks',// 值为占位符，如需使用APIv2请替换为实际值
		    // 'merchant' => [// --不使用APIv2可选
		    //     // 商户证书 文件路径 --不使用APIv2可选
		    //     'cert' => $merchantCertificateFilePath,
		    //     // 商户API私钥 文件路径 --不使用APIv2可选
		    //     'key' => $merchantPrivateKeyFilePath,
		    // ],
		]);
		
		$appid = $wxpay_config['appid'];
		if($os_type == 'android'){
			$appid = $wxpay_config['app_appid'];
		}
		try {
			$total = floatval($order['total']) * 100;
			// $total = intval(1); // debug
		    $resp = $instance
		    ->v3->pay->transactions->app
		    ->post(['json' => [
		        'mchid'        => $merchantId,
		        'out_trade_no' => $order['orderid'],
		        'appid'        => $appid,
		        'description'  => $order['name'],
		        'notify_url'   => $wxpay_config['notify_url'],
		        'amount'       => [
		            'total'    => $total,
		            'currency' => 'CNY'
		        ],
		    ]]);
			error_log(print_r($wxpay_config['notify_url'], true) . "\r\n", 3, '/tmp/url-debug.log');
			$res = $resp->getBody()->getContents();
			if(strpos($res, 'prepay_id') === false){
				$ret['msg'] = '下单失败';
				return json($ret);
			}
			$prepay = json_decode($res, true);
			$data = [
				'appid' => $appid,
				'partnerid' => $merchantId,
				'prepayid' => $prepay['prepay_id'],
				'package' => 'Sign=WXPay',
				'noncestr' => uniqid(),
				'timestamp' => time()
			];
			$data['sign'] = $this->_signStr($data);
			
		// error_log(print_r($data, true) . "\r\n", 3, '/Volumes/mac-disk/work/www/debug.log');
		  //  echo $resp->getStatusCode(), PHP_EOL;
		  //  echo $resp->getBody(), PHP_EOL;
		} catch (\Exception $e) {
			$msg = '付款失败 code wx_00001';
			if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
			    $r = $e->getResponse();
			    $msg = 'error: ' . $r->getStatusCode() . ' ' . $r->getReasonPhrase() . ' ' . $r->getBody();
			}
			$ret['msg'] = $msg;
			return json($ret);
			/*
		    // 进行错误处理
		    echo $e->getMessage(), PHP_EOL;
		    if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
		        $r = $e->getResponse();
		        echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
		        echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
		    }
		    echo $e->getTraceAsString(), PHP_EOL;
			*/
		}

		
		// {"appid":"wx0411fa6a39d61297","noncestr":"Y6XCXVOoUJoc7MHR","package":"Sign=WXPay","partnerid":"1230636401","prepayid":"wx0822052722326175440bc04e880e6a0000","timestamp":1631109927,"sign":"20E78BBCAE30F898C4E9ED958DF5F1FC"}
		$ret['data'] = $data;
		$ret['code'] = 1;
		return json($ret);
	}

	private function _signStr($arr){
		ksort($arr);
		$str = urlencode(http_build_query($arr));
		$sign = strtoupper(md5($str));
		return $sign;
	}
	
	/**
	 * 付款回调
	 */
	public function frontReceive(){

		$merId = Request::param('merId');
		$orderId = Request::param('orderId');
		$respCode = Request::param('respCode');
		$respMsg = Request::param('respMsg');
		Log::write('unionpay receive orderid: ' . $orderId . ' in', 'unionpay-web');
		if(! isset ( $_POST ['signature'] )){
			Log::write('unionpay receive check error orderid: ' . $orderId, 'unionpay-web');
			echo '非法操作 code: 40001';
			exit;
		}
		$check = \com\unionpay\acp\sdk\AcpService::validate ( $_POST );
		if(! $check){
			Log::write('unionpay receive check error orderid: ' . $orderId, 'unionpay-web');
			echo '非法操作 code: 40002';
			exit;
		}
		
		if(Config::get('app.unionpay_merid') != $merId){
			Log::write('unionpay receive merid error orderid: ' . $orderId . ' merid: ' . $merId, 'unionpay-web'); 
			echo '非法操作 code: 40003';
			exit;
		}
		
		if($respCode != '00' || $respMsg != 'success'){
			Log::write('unionpay receive msg_code error orderid: ' . $orderId . ' msg code: ' . $respCode . ' ' . $respMsg, 'unionpay-web');
			echo '非法操作 code: 40004';
			exit;
		}
		
		$date = date('Y-m-d H:i:s');
		// 修改订单状态
		$update_data = [
			'status' => 1,
			'pay_type' => 3,
			'updated_date' => $date
		];
		$update_where = [
			'orderid' => $orderId,
			'status' => 0
		];
		$update_status = Db::name('order')
			->where($update_where)
			->update($update_data);
		if($update_status === false){
			Log::write('unionpay receive update sql error orderid: ' . $orderId . ' sql: ' . Db::getLastSql(), 'unionpay-web');
			echo '付款失败';
			exit;
		}
		$url = $this->url('/Home');
		header('Location: ' . $url);
		exit;
	}
	
	/**
	 * 付款回调
	 */
	public function frontReceiveApp(){

		$merId = Request::param('merId');
		$orderId = Request::param('orderId');
		$respCode = Request::param('respCode');
		$respMsg = Request::param('respMsg');
		Log::write('unionpay receive orderid: ' . $orderId . ' in', 'unionpay-app');
		if(! isset ( $_POST ['signature'] )){
			Log::write('unionpay receive signature error orderid: ' . $orderId, 'unionpay-app'); 
			echo '非法操作 code: 40001';
			exit;
		}
		$check = \com\unionpay\acp\sdk\AcpService::validate ( $_POST );
		if(! $check){
			Log::write('unionpay receive check error orderid: ' . $orderId, 'unionpay-app'); 
			echo '非法操作 code: 40002';
			exit;
		}
		
		if(Config::get('app.unionpay_merid') != $merId){
			Log::write('unionpay receive merid error orderid: ' . $orderId . ' merid: ' . $merId, 'unionpay-app'); 
			echo '非法操作 code: 40003';
			exit;
		}
		
		if($respCode != '00' || $respMsg != 'success'){
			Log::write('unionpay receive msg_code error orderid: ' . $orderId . ' msg code: ' . $respCode . ' ' . $respMsg, 'unionpay-app');
			echo '非法操作 code: 40004';
			exit;
		}
		
		$date = date('Y-m-d H:i:s');
		// 修改订单状态
		$update_data = [
			'status' => 1,
			'pay_type' => 3,
			'updated_date' => $date
		];
		$update_where = [
			'orderid' => $orderId,
			'status' => 0
		];
		$update_status = Db::name('order')
			->where($update_where)
			->update($update_data);
		if($update_status === false){
			Log::write('unionpay receive update sql error orderid: ' . $orderId . ' sql: ' . Db::getLastSql(), 'unionpay-app');
			echo '付款失败';
			exit;
		}
		$url = $this->url('/Home/apppay');
		header('Location: ' . $url);
		exit;
	}
	
	public function backReceive(){

		$merId = Request::param('merId');
		$orderId = Request::param('orderId');
		$respCode = Request::param('respCode');
		$respMsg = Request::param('respMsg');
		Log::write('unionpay receive orderid: ' . $orderId . ' in', 'unionpay-back');
		if(! isset ( $_POST ['signature'] )){
			Log::write('unionpay receive signature error orderid: ' . $orderId, 'unionpay-back'); 
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		$check = \com\unionpay\acp\sdk\AcpService::validate ( $_POST );
		if(! $check){
			Log::write('unionpay receive check error orderid: ' . $orderId, 'unionpay-back'); 
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		
		if(Config::get('app.unionpay_merid') != $merId){
			Log::write('unionpay receive merid error orderid: ' . $orderId . ' merid: ' . $merId, 'unionpay-back'); 
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		
		if($respCode != '00' || $respMsg != 'success'){
			Log::write('unionpay receive msg_code error orderid: ' . $orderId . ' msg code: ' . $respCode . ' ' . $respMsg, 'unionpay-back');
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		
		$date = date('Y-m-d H:i:s');
		// 修改订单状态
		$update_data = [
			'status' => 1,
			'pay_type' => 3,
			'updated_date' => $date
		];
		$update_where = [
			'orderid' => $orderId,
			'status' => 0
		];
		$update_status = Db::name('order')
			->where($update_where)
			->update($update_data);
		if($update_status === false){
			Log::write('unionpay receive update sql error orderid: ' . $orderId . ' sql: ' . Db::getLastSql(), 'unionpay-back');
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		echo 'ok';
		exit;
	}
}
