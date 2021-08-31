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
	
	public $order_status_list = [
		0 => '待付款',
		1 => '已付款',
		2 => '付款失败',
		3 => '已退款',
		4 => '已取消'
	];
	
	public $cashout_status_list = [
		0 => '待支付',
		1 => '已支付',
		// 2 => '废弃'
	];
    /*
    public function index()
    {
		$this->is_check_login = false;
		$url = $this->url('/index.php?s=admin/login');
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
	}
	*/

    public function taskList(){
		$list = Db::name('task')
			->field('id, name, added_date')
            ->where(1)
            ->order('id', 'desc')
			->select();
		
		View::assign('list', $list);
		$url = $this->url('/index.php?s=Admin/taskEdit');
		View::assign('url', $url);
		return View::fetch('taskList');
    }

    public function cashoutList(){
		View::assign('cashout_status_list', $this->cashout_status_list);
		return View::fetch('cashoutList');
    }
	
	public function ajaxCashoutList(){
		
		$where = [];
		
		$status = intval(Request::param('status'));
		if(isset($_GET['status']) && $_GET['status'] !== null && $_GET['status'] !== ''){
			$where['a.status'] = $status;
		}
		
		$count = Db::name('cashout_record')
			->alias('a')
			->join('user b', 'a.userid = b.id', 'left')
			->join('user_card c', 'a.userid = c.userid', 'left')
			->where($where)
			->field('count(*) as count')
			->select();
			
		$list = Db::name('cashout_record')
			->alias('a')
			->join('user b', 'a.userid = b.id', 'left')
			->join('user_card c', 'a.userid = c.userid', 'left')
			->where($where)
			->field('a.*, b.mobile, c.wx_qrcode, c.ali_qrcode')
		    ->order('a.id', 'desc')
			->select();
		
		$url = $this->url('/index.php?s=Admin/cashoutEdit');

		$ret = [
			'code' => 0,
			'count' => $count[0]['count'],
			'data' => $list,
			'msg' => ''
		];
		return json($ret);
	}

    public function cashoutEdit(){
		$url = $this->url('/Admin/actionCashoutEditAction');
		View::assign('url', $url);
		$id = intval(Request::param('id'));
		$where['a.id'] = $id;
		$data = Db::name('cashout_record')
			->alias('a')
			->join('user b', 'a.userid = b.id', 'left')
			->join('user_card c', 'a.userid = c.userid', 'left')
			->where($where)
			->field('a.id, a.amount, b.mobile, a.status')
			->find();
			
		View::assign('data', $data);
		View::assign('cashout_status_list', $this->cashout_status_list);
        return View::fetch('cashoutEdit');
    }

    public function actionCashoutEditAction(){
        $ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
        $id = intval(Request::param('id'));
        $status = intval(Request::param('status'));
		if(! $id){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$date = date('Y-m-d H:i:s');
		$data = [
			'status' => $status,
			'updated_by' => $this->userid,
			'updated_date' => $date,
        ];
        $where = [
            'id' => $id
        ];
		$status = Db::name('cashout_record')->where($where)->update($data);
		if(! $status){
			$ret['msg'] = '更新失败';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
    }

    public function userList(){
        $where = 'a.`status` = 1 ';
        $keyword = addslashes(trim(Request::param('keyword')));
        if($keyword){
        	$where .= 'and a.mobile like "%' . $keyword . '%"';
        }
		$field = 'a.id, a.mobile, a.added_date, count(distinct b.invite_userid) as count1, count(c.invite_userid) as count2';
        $list = Db::name('user')->alias('a')
        	->join('user_invite b', 'a.id = b.userid', 'left')
			->join('user_invite c', 'b.invite_userid = c.userid', 'left')
        	->field($field)
            ->where($where)
            ->order('a.id', 'desc')
			->group('a.id')
        	->select();
        
        $ret['code'] = 1;
        $ret['data'] = $list;
        View::assign('list', $list);
        View::assign('keyword', $keyword);
        return View::fetch('userList');
    }

    public function orderList(){
		
		$where = '';
		$keyword = addslashes(trim(Request::param('keyword')));
		if($keyword){
			$where .= 'a.orderid like "%' . $keyword . '%" or b.mobile like "%' . $keyword . '%"';
		}
		$list = Db::name('order')->alias('a')
			->join('user b', 'a.userid = b.id', 'left')
			->field('a.id, a.orderid, a.total, a.status, a.added_date, b.mobile')
            ->where($where)
            ->order('id', 'desc')
			->select();
		
		$ret['code'] = 1;
		$ret['data'] = $list;
		View::assign('list', $list);
		View::assign('keyword', $keyword);
		View::assign('order_status_list', $this->order_status_list);
		return View::fetch('orderList');
    }

    public function taskEdit(){
		$url = $this->url('/index.php?s=Admin/actionTaskAdd');
		View::assign('url', $url);
        return View::fetch('taskEdit');
    }

    public function actionTaskEditAction(){
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

    public function actionTaskAdd(){
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
