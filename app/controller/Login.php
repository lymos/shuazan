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
    
    public function __construct(\think\App $app)
    {
        parent::__construct($app, false);
    }

	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 * 返回token API 访问携带token
	 */
	public function login(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		
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
			['login_code_expire', '>', $time]
		];
		
		$data = Db::name('user')
			->field('id')
			->where($where)->find();
		if(! $data){
			$ret['msg'] = '手机号或验证码错误';
			return json($ret);
		}
		$token_data = $this->setLoginSession($data['id']);
		if($token_data === false){
			$ret['msg'] = '登录失败';
			return json($ret);
		}
		$ret['data'] = $token_data;
		$ret['code'] = 1;
		return json($ret);
	}
	
	public function setLoginSession($userid){
		$token = md5(md5('doucc' . rand(10000, 99999) . $userid));
		$data = [
			'token' => $token,
			'token_expire' => time() + (24 * 3600) * 5,
			
		];
		$status = Db::name('user')
			->where(['id' => $userid])
			->update($data);
		if($status){
			$data['userid'] = $this->encrypt($userid);
			return $data;
		}
		return false;
	}
	
	private function _checkMobile($mobile){
		$chars = "/^((\(\d{2,3}\))|(\d{3}\-))?1(3|5|8|9)\d{9}$/";
		if (preg_match($chars, $mobile)){
		    return true;
		}else{
		    return false;
		}
	}
	
	private function _genCode(){
		return rand(100001, 999998);
	}
	
	
	/**
	 * 短信验证码
	 */
	public function regSmsCode(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$mobile = trim(Request::param('mobile'));
		if(! $mobile){
			$ret['msg'] = '请输入手机号';
			return json($ret);
		}
		if(! $this->_checkMobile($mobile)){
			$ret['msg'] = '请输入正确的手机号';
			return json($ret);
		}
		
		$old_data = $this->_checkIsExists($mobile);
		$is_update = false;
		if($old_data && $old_data['status'] == 1){
			$ret['msg'] = '该手机号已经被注册';
			return json($ret);
		}else if($old_data && ! $old_data['status']){
			$is_update = true;
		}
		
		$code = $this->_genCode();
		$msg = '【抖财财】您的注册验证码是：' . $code;
		// $res = $this->sendSms($mobile, $msg); // debug
		$res = true; // debug
		if(! $res){
			$ret['msg'] = '短信发送失败';
			return json($ret);
		}
		$now = time();
		$date = date('Y-m-d H:i:s');
		
		if(! $is_update ){
			// 插入数据
			$data = [
				'mobile' => $mobile,
				'signup_code' => $code,
				'signup_code_expire' => $now + 300,
				'added_date' => $date
			];
			$userid = Db::name('user')->insertGetId($data);
			if(! $userid){
				$ret['msg'] = '短信发送失败, code 10001';
				return json($ret);
			}
		}else{
			$update_data = [
				'signup_code' => $code,
				'signup_code_expire' => $now + 300,
			];
			$update_status = Db::name('user')->where(['id' => $old_data['id']])->update($update_data);
			if(! $update_status){
				$ret['msg'] = '短信发送失败, code 10002';
				return json($ret);
			}
		}
		$ret['code'] = 1;
		return json($ret);
	}
	
	private function _checkIsExists($mobile){
		$data = Db::table('dcc_user')
			->field('id, status')
			->where(['mobile' => $mobile])->find();
		return $data;
	}
	
	/**
	 * 短信验证码
	 */
	public function smsCode(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$mobile = trim(Request::param('mobile'));
		if(! $mobile){
			$ret['msg'] = '请输入手机号';
			return json($ret);
		}
		if(! $this->_checkMobile($mobile)){
			$ret['msg'] = '请输入正确的手机号';
			return json($ret);
		}
		$old_data = $this->_checkIsExists($mobile);
		if(! $old_data || ($old_data && ! $old_data['status'])){
			$ret['msg'] = '该手机号未注册';
			return json($ret);
		}
		
		$code = $this->_genCode();
		$msg = '【抖财财】您的登录验证码是：' . $code;
		// $res = $this->sendSms($mobile, $msg); // debug
		$res = true; // debug
		if(! $res){
			$ret['msg'] = '短信发送失败';
			return json($ret);
		}
		$now = time();
		$update_data = [
			'login_code' => $code,
			'login_code_expire' => $now + 300,
		];
		$update_status = Db::name('user')->where(['id' => $old_data['id']])->update($update_data);
		if(! $update_status){
			$ret['msg'] = '短信发送失败, code 10003';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
	}
	
	/**
	 * 发送验证码时，先user表插入一条数据，存验证码
	 * 注册 返回token API 访问携带token
	 */
	public function reg(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		
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
			['signup_code', '=', $verify_code],
			['signup_code_expire', '>', $time]
		];
		
		$data = Db::name('user')
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
		$update_status = Db::name('user')
			->where(['id' => $data['id']])
			->update($update_data);
		
		if($update_status === false){
			$ret['msg'] = '发生错误';
			return json($ret);
		}
		
		$token_data = $this->setLoginSession($data['id']);
		if($token_data === false){
			$ret['msg'] = '登录失败';
			return json($ret);
		}
		$ret['data'] = $token_data;
		$ret['code'] = 1;
		return json($ret);
	}
}
