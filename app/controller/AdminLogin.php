<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User; 
use think\facade\Request;
use think\facade\Db;

class AdminLogin extends BaseController
{
    
    public function index()
    {
		$url = $this->url('/index.php?s=adminlogin/login');
		View::assign('url', $url);
		$task_list_url = $this->url('/index.php?s=admin/taskList');
		View::assign('task_list_url', $task_list_url);
        return View::fetch('login');
    }

    public function login(){
		$this->is_check_login = false;
        $ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
        $mobile = trim(Request::param('mobile'));
        $pwd = trim(Request::param('pwd'));
		if(! $mobile || ! $pwd){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$pwd = md5(md5($pwd . 'dcc'));
		$where = [
			'mobile' => $mobile,
            'pwd' => $pwd,
            'type' => 1
		];
		$data = Db::name('user')
			->field('id')
            ->where($where)->find();
        if($data){
            $ret['code'] = 1;
            $this->setLogin($data['id']);
        }else{
            $ret['msg'] = '账号或密码错误';
        }
		
		//$url = $this->url('/index.php?s=admin/taskList');
		//return redirect($url);
		
		return json($ret);
    }
	
	public function logout(){
		$this->setLogout();
		$login_url = $this->url('/index.php?s=adminlogin');
		header('Location: ' . $login_url);
		die;
	}
	
	public function setLogin($userid){
	    session('userid', $userid);
		// error_log(print_r($userid, true) . "\r\n", 3, '/Volumes/mac-disk/work/www/debug.log');
		// error_log(print_r(session('userid'), true), 3, '/Volumes/mac-disk/work/www/debug.log'); die;
		cookie('userid', $userid);
	    $token = md5('dcc' . $userid . '8923');
	    session('token', $token);
		cookie('token', $token);
	    return true;
	}
	
	public function setLogout(){
	    session(null);
		cookie('userid', null);
		cookie('token', null);
	    return true;
	}
}
