<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;

 
class Order extends BaseController
{
 
	
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function createOrder(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$productid = intval($this->decrypt(Request::param('productid')));
		$userid = intval($this->decrypt(Request::param('userid')));
		if(! $productid || ! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		
		// 查询产品信息
		$product = Db::name('product')
			->field('id,price')
			->where('id', $productid)->find();
		if(! $product){
			$ret['msg'] = '产品不存在';
			return json($ret);
		}
		
		// 创建订单号
		$orderid = $this->_genOrderid();
		if(! $orderid){
			$ret['msg'] = '购买失败';
			return json($ret);
		}
		
		$date = date('Y-m-d H:i:s');
		$data = [
			'orderid' => $orderid,
			'total' => $product['price'],
			'userid' => $userid,
			'added_by' => $userid,
			'added_date' => $date
		];
		Db::startTrans();
		$id = Db::name('order')->insertGetId($data);
		if($id === false){
			Db::rollback();
			$ret['msg'] = '发生错误 code 20001';
			return json($ret);
		}
		
		$detail = [
			'orderid' => $id,
			'productid' => $productid
		];
		$detail_status = Db::name('order_detail')->insert($detail);
		if($detail_status === false){
			Db::rollback();
			$ret['msg'] = '发生错误 code 20002';
			return json($ret);
		}
		Db::commit();
		
		$res = [
			'orderid' => $this->encrypt($orderid),
			'userid' => $this->encrypt($userid)
		];
		
		
		$ret = [
			'code' => 1,
			'data' => $res,
			'msg' => ''
		];
		return json($ret);
	}
	
	private function _genOrderid(){
		$orderid = '';
		$prefix = 'dcc' . date('Ymd');
		$order = Db::name('order')
			->field('orderid')
			->where([['orderid', 'like', $prefix . '%']])
			->order('id', 'desc')
			->limit(1)
			->find();
			
		if(! $order){
			$orderid = $prefix . '0001';
		}else{
			$num = intval(substr($order['orderid'], -4));			
			$num = $num + 1;
			$len = strlen($num);
			switch($len){
				case 1:
					$num = '000' . $num;
					break;
				case 2:
					$num = '00' . $num;
					break;
				case 3:
					$num = '0' . $num;
					break;
			}
			$orderid = $prefix . $num;
		}
		return $orderid;
	}
}
