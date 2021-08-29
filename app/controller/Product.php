<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;

 
class Product extends BaseController
{
    
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function getProductList(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		
		$userid = intval($this->decrypt(Request::param('userid')));
		if(! $userid ){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		
		$data = Db::name('product')->field('id, name, price')->select()
			->toArray();
		
		$productids = array_column($data, 'id');
		
		// 查询是否已经购买
		$where = [
			'a.userid' => $userid,
			'a.status' => 1,
			'b.productid' => $productids
		];
		$buy_data = Db::name('order')->alias('a')
			->join('order_detail b', 'a.id = b.orderid', 'left')
			->field('b.productid')
			->where($where)
			->select()->toArray();
		
		$buy_data_ids = array_column($buy_data, 'productid');
		foreach($data as & $rs){
			if(in_array($rs['id'], $buy_data_ids)){
				$rs['is_buy'] = true;
			}else{
				$rs['is_buy'] = false;
			}
			$rs['id'] = $this->encrypt($rs['id']);
		}		
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}
}
