<?php
namespace app\controller;

use app\BackController;
use think\facade\View;
use app\model\User; 
use think\facade\Request;
use think\facade\Db;
// use think\App;

class Admin extends BackController
{

    public function __construct()
    {
       // parent::__construct(new App);
    }
    
    public function index()
    {
		$host = $_SERVER['HTTP_HOST'];
		$scheme = $_SERVER['REQUEST_SCHEME'];
		$url = $scheme . '://' . $host . '/index.php?s=admin/login';
		View::assign('url', $url);
        return View::fetch('login');
    }

    public function login(){
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
		
		$url = $this->url('/index.php?s=admin/taskList');
		return redirect($url);
		
		return json($ret);
    }

    public function taskList(){
        $ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];

		$list = Db::name('task')
			->field('id, name')
            ->where(1)
            ->order('id', 'desc')
			->select();
		
		$ret['code'] = 1;
		$ret['data'] = $list;
		View::assign('list', $list);
		return View::fetch('taskList');
		return json($ret);
    }

    public function taskEdit(){
        return View::fetch('taskedit');
    }

    public function taskEditAction(){
        $ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
        $id = trim(Request::param('id'));
        $name = trim(Request::param('name'));
        $is_enabled = trim(Request::param('is_enabled'));
		if(!$id || ! $name){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$date = date('Y-m-d H:i:s');
		$data = [
			'name' => $name,
			'updated_by' => $this->userid,
			'updated_date' => $date,
			'is_enabled' => $is_enabled
        ];
        $where = [
            'id' => $id
        ];
		$status = Db::name('task')->where($where)->update($data);
		if(! $status){
			$ret['msg'] = '更新失败';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
    }

    public function addTaskAction(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$name = trim(Request::param('name'));
		if(! $name){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$date = date('Y-m-d H:i:s');
		$data = [
			'name' => $name,
			'added_by' => $this->userid,
			'added_date' => $date,
			
		];
		$status = Db::name('task')->insert($data);
		if(! $status){
			$ret['msg'] = '添加失败';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
	}
}
