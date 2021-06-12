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
		
		$data = Db::name('product')->where('id', 1)->find();
		$productid = 0;
		if(isset($data['id'])){
			$productid = $data['id'];
			$data['id'] = $this->encrypt($data['id']);
		}
		
		// 查询是否已经购买
		$where = [
			'a.userid' => $userid,
			'a.status' => 1,
			'b.productid' => $productid
		];
		$isbuy = Db::name('order')->alias('a')
			->join('order_detail b', 'a.id = b.orderid', 'left')
			->field('a.id')
			->where($where)
			->find();
		
		$ret = [
			'code' => 1,
			'data' => [
				'data' => $data,
				'isbuy' => $isbuy ? true : false
			],
			'msg' => ''
		];
		return json($ret);
	}
}
