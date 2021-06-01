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
 
class Cash extends BaseController
{
   
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 * 提现
	 */
	public function cashout(){
		if(! $this->verify_token()){
			return json(['code' => 0, 'data' => '', 'msg' => 'token is invaild']);
		}
		$data = Db::table('dcc_user')
			->field('invite_code, mobile, id')
			->where('id', 1)->find();
		$invite_data = $this->getUserInvite();
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}
	
	public function unionpay(){
		$merid = Config::get('app.unionpay_merid');
		
		$userid = intval(Request::param('userid'));
		$orderid = intval(Request::param('orderid'));
		$orderid = 'dcc202105300002'; // debug
		// 查处订单信息
		$order = $this->_getOrder($userid, $orderid);
		if(! $order){
			echo '订单不存在';
			exit;
		}
		$total = $order['total'];
		$time = date('YmdHis');
		$amt = $total;
		$params = array(
				
				//以下信息非特殊情况不需要改动
				'version' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,                 //版本号
				'encoding' => 'utf-8',				  //编码方式
				'txnType' => '01',				      //交易类型
				'txnSubType' => '01',				  //交易子类
				'bizType' => '000201',				  //业务类型
				'frontUrl' =>  \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->frontUrl,  //前台通知地址
				'backUrl' => \com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->backUrl,	  //后台通知地址
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
		
				'riskRateInfo' =>'{commodityName=测试商品名称}',
		
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
		// error_log(print_r($html_form, true), 3, '/Volumes/mac-disk/work/www/debug.log');
	}
	
	private function _getOrder($userid, $orderid){
		$where = [
			'orderid' => $orderid,
			'userid' => $userid,
			'status' => 0
		];
		$order = Db::name('order')
			->field('total')
			->where($where)->find();
		return $order;
	}
	
	public function frontReceive(){
		error_log(print_r('frontReceive', true), 3, '/Volumes/mac-disk/work/www/debug.log');
		error_log(print_r($_POST, true), 3, '/Volumes/mac-disk/work/www/debug.log');
		error_log(print_r($_GET, true), 3, '/Volumes/mac-disk/work/www/debug.log');
		$merId = Request::param('merId');
		$orderId = Request::param('orderId');
		$respCode = Request::param('respCode');
		$respMsg = Request::param('respMsg');
		
		if(Config::get('app.unionpay_merid') != $merId){
			echo '非法操作';
			exit;
		}
		
		if($respCode != '00' && $respMsg != 'success'){
			echo '付款失败';
			exit;
		}
		
		$date = date('Y-m-d H:i:s');
		// 修改订单状态
		$update_data = [
			'status' => 1,
			'updated_date' => $date
		];
		$update_where = [
			'orderid' => $orderId
		];
		$update_status = Db::name('order')
			->where($update_where)
			->update($update_data);
		if($update_status === false){
			echo '付款失败';
			exit;
		}
		echo '付款成功<a href="">首页</a>';
		exit;
	}
	
	public function backReceive(){
		error_log(print_r('frontReceive', true), 3, '/Volumes/mac-disk/work/www/debug.log');
		error_log(print_r($_POST, true), 3, '/Volumes/mac-disk/work/www/debug.log');
		error_log(print_r($_GET, true), 3, '/Volumes/mac-disk/work/www/debug.log');
		
	}
}