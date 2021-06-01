<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
 
class Task extends BaseController
{
    
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function get_task(){
		if(! $this->verify_token()){
			return json(['code' => 0, 'data' => '', 'msg' => 'token is invaild']);
		}
		$data = Db::table('dcc_task')->where('id', 1)->find();
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}

	/**
	 * 领取任务
	 */
	public function takeTask(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$taskid = trim(Request::param('taskid'));
		$userid = trim(Request::param('userid'));
		if(! $taskid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$date = date('Y-m-d');

		if(! $this->_checkCanTake($userid, $taskid, $date)){
			$ret['msg'] = '您不能领取此任务';
			return json($ret);
		}
		
		$data = [
			'taskid' => $taskid,
			'userid' => $userid,
			'date' => $date,
			'status' => 1,
			'process' => 100
		];
		$status = Db::name('user_task')->insert($data);
		if(! $status){
			$ret['msg'] = '领取失败';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
	}

	private function _checkCanTake($userid, $taskid, $date){
		$old_data = Db::name('user_task')
			->field('id')
			->where([
				'taskid' => $taskid,
				'userid' => $userid,
				'date' => $date
			])->find();
		if($old_data){
			return false;
		}

		$order = Db::name('order')
			->field('id')
			->where(
				[
					'userid' => $userid,
					'status' => 1,
					'total' => '1800'
				]
			)->find();
		if(! $order){
			return false;
		}
		return true;
	}
}
