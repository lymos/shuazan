<?php
namespace app\controller;

use app\BackController;
use think\facade\View;
use app\model\User; 
use think\facade\Request;
use think\facade\Db;

class Home extends BaceController
{
    public function index()
    {
        return View::fetch('index');
    }

    public function login()
    {
        return View::fetch('login');
    }

    public function reg()
    {
        return View::fetch('reg');
    }

    public function card()
    {
        return View::fetch('card');
    }

    public function cashout()
    {
        return View::fetch('cashout');
    }

    public function invite()
    {
        return View::fetch('invite');
    }

    public function rule()
    {
        return View::fetch('rule');
    }

    public function shop()
    {
        return View::fetch('shop');
    }

    public function signin()
    {
        return View::fetch('signin');
    }

    public function tabgain()
    {
        return View::fetch('tab-gain');
    }

    public function tabme()
    {
        return View::fetch('tab-me');
    }

    public function tabtask()
    {
        return View::fetch('tab-task');
    }
   
}
