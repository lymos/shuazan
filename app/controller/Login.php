<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;
 
class Login extends BaseController
{
    
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function login(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		if(! $this->verify_token()){
			$ret['msg'] = 'token is invaild';
			return json($ret);
		}
		$mobile = Request::param('mobile');
		$verify_code = Request::param('verify_code');
		if(! trim($mobile) || ! trim($verify_code)){
			$ret['msg'] = '手机号或验证码不能为空';
			return json($ret);
		}
		$time = time();
		$where = [
			['mobile', '=', $mobile],
			['status', '=', 1],
			['login_code', '=', $verify_code],
			['login_code_expire', '<', $time]
		];
		
		$data = Db::table('dcc_user')
			->field('id')
			->where($where)->find();
		if(! $data){
			$ret['msg'] = '手机号或验证码错误';
			return json($ret);
		}
		$this->setLoginSession($data[id]);
		$ret['code'] = 1;
		return json($ret);
	}
	
	public function setLoginSession($userid){
		
	}
	
	/**
	 * 发送验证码时，先user表插入一条数据，存验证码
	 * 注册
	 */
	public function reg(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		if(! $this->verify_token()){
			$ret['msg'] = 'token is invaild';
			return json($ret);
		}
		$mobile = Request::param('mobile');
		$invite_code = Request::param('invite_code');
		$verify_code = Request::param('verify_code');
		if(! trim($mobile) || ! trim($verify_code)){
			$ret['msg'] = '手机号或验证码不能为空';
			return json($ret);
		}
		$time = time();
		$where = [
			['mobile', '=', $mobile],
			['login_code', '=', $verify_code],
			['login_code_expire', '<', $time]
		];
		
		$data = Db::table('dcc_user')
			->field('id')
			->where($where)->find();
		if(! $data){
			$ret['msg'] = '手机号或验证码错误';
			return json($ret);
		}
		$new_invite_code = 'DCC' . $data['id'];
		$update_data = [
			'status' => 1,
			'invite_code' => $new_invite_code
		];
		$update_status = Db::table('dcc_user')
			->where(['id' => $data['id']])
			->update($update_data);
		
		if($update_status === false){
			$ret['msg'] = '发生错误';
			return json($ret);
		}
		
		$this->setLoginSession($data[id]);
		$ret['code'] = 1;
		return json($ret);
	}
}
