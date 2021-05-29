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
		$this->sendSms();
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
		$code = $this->_genCode();
		$msg = '【抖财财】您的注册验证码是：' . $code;
		$ret = $this->sendSms($mobile, $msg);
		if(! $ret){
			$ret['msg'] = '短信发送失败';
			return json($ret);
		}
		$now = time();
		$date = date('Y-m-d H:i:s');
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
		$ret['data'] = $userid;
		return json($ret);
	}
}
