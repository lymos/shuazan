<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
 
class Order extends BaseController
{
 
	
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function get_product(){
		if(! $this->verify_token()){
			return json(['code' => 0, 'data' => '', 'msg' => 'token is invaild']);
		}
		$data = Db::table('dcc_product')->where('id', 1)->find();
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}
}
