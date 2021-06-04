<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;
 
class DccUser extends BaseController
{
    public function index()
    {
        print_r(User::getList()->toArray());
        return View::fetch('index');
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
	
	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function info(){
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
	
	/**
	 * 获取已邀请用户
	 * 二级用户数（二级用户所邀请人数）
	 */
	public function getUserInvite(){
		$data = Db::table('dcc_user')
			->field('invite_code, mobile, id')
			->where('id', 1)->find();
		return $data;
	}
	
	/**
	 * add card
	 */
	public function addCard(){
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
	
	/**
	 * 生成二维码和邀请码
	 */
	public function getInviteInfo(){
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
	
	/**
	 * 获取用户收益信息
	 */
	public function getUserGain(){
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
		return json($ret);
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
		
		return json($ret);
	}
	
	private function _checkIsExists($mobile){
		$data = Db::table('dcc_user')
			->field('id, status')
			->where(['mobile' => $mobile])->find();
		return $data;
	}
	
	/**
	 * 获取签到数据
	 */
	public function signinInfo(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = trim(Request::param('userid'));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$where = [
			'userid' => $userid,
			'status' => 0
		];
		$data = Db::name('user_signin')
			->field('signin_date, days')
			->where($where)->select();
		$ret['data'] = [
			'days' => count($data),
			'list' => $data
		];
		return json($ret);
	}
	
	/**
	 * 签到
	 */
	public function signin(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = trim(Request::param('userid'));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
	
		$date = date('Y-m-d');
		$time = date('Y-m-d H:i:s');
		
		Db::startTrans();
		// 判断是否连续 不连续需把之前的废弃
		$days = $this->_findContinue($userid, $date);
		if(! $days){
			Db::rollback();
			$ret['msg'] = '发生错误';
			return json($ret);
		}

		$data = [
			'userid' => $userid,
			'signin_date' => $date,
			'days' => $days,
			'added_date' => $time
		];
		$res = Db::name('user_signin')->insert($data);
		if($res === false){
			Db::rollback();
			$ret['msg'] = '发生错误';
			return json($ret);
		}
		if($days == 14){
			// 满送1个邀请用户
			$insert_data = [
				'mobile' => uuid_v5('name'),
				'type' => 2,
				'added_date' => $time
			];
			$insert_userid = Db::name('user')->insertGetId($insert_data);
			if($insert_userid === false){
				Db::rollback();
				$ret['msg'] = '发生错误';
				return json($ret);
			}
			$invite_data = [
				'userid' => $userid,
				'invite_userid' => $insert_userid
			]
			$invite_status = Db::name('user_invite')->insertGetId($invite_data);
			if($invite_status === false){
				Db::rollback();
				$ret['msg'] = '发生错误 code 30001';
				return json($ret);
			}
		}

		Db::commit();
		return json($ret);
	}
	
	private function _findContinue($userid, $date){
		$days = 0;
		$perv = date('Y-m-d', strtotime(strtotime($data), '-1 day'));
		$where = [
			['status', '=', 0],
			['userid', '=', $userid],
			['signin_date', '=', $perv]
		];
		$data = Db::name('user_signin')
			->field('id, days')
			->where($where)->find();
		if(! $data){
			$days = 1;
			// 以前的设置为废弃
			$update_data = [
				'status' => 2
			];
			$update_where = [
				'status' => 0,
				'userid' => $userid
			];
			$update_status = Db::name('user_signin')
				->where($update_where)->update($update_data);
			if($update_status === false){
				return false;
			}
		}else{
			$days = $data['days'] + 1;
		}
		return $days;
	}
}
