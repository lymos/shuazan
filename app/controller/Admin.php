<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;

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
}
