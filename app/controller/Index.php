<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;

class Index extends BaseController
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
	
	public function get_product(){
		$data = Db::table('product')->where('id', 1)->find();
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}
}
