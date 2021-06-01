<?php
/**
 * 
 */
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
 
class Gain extends BaseController
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
	
	public function addCard(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$mobile = trim(Request::param('mobile'));
		$card = trim(Request::param('card'));
		$userid = trim(Request::param('userid'));
		if(! $mobile || ! $card){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$date = date('Y-m-d H:i:s');

		if($this->_checkCardIsExists($card, $userid)){
			$ret['msg'] = '您不能添加此卡';
			return json($ret);
		}
		
		$data = [
			'userid' => $userid,
			'card' => $card,
			'added_by' => $userid,
			'mobile' => $mobile,
			'added_date' => $date
		];
		$status = Db::name('user_card')->insert($data);
		if(! $status){
			$ret['msg'] = '领取失败';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
	}

	private function _checkCardIsExists($card, $userid){
		$data = Db::name('user_card')
			->field('id')
			->where(['card' => $card])
			->where_or(['userid' => $userid])
			->find();
		if($data){
			return true;
		}
		return false;
	}
}
