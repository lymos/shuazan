<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User; 
use think\facade\Request;
use think\facade\Db;

class Home extends BaseController
{
	public $app_android_ver = '1.01';
	
	public function __construct(\think\App $app){
		parent::__construct($app, false);
	}
	
	public function getAndroidAppVer(){
		echo $this->$app_android_ver;
		exit;
	}
	
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
		$invite_code = trim(Request::param('invite_code') ? Request::param('invite_code') : '');
		View::assign('invite_code', $invite_code);
        return View::fetch('reg');
    }

    public function card()
    {
        return View::fetch('card');
    }
	
	public function apppay()
	{
	    return View::fetch('apppay');
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
	
	public function taskall()
	{
	    return View::fetch('taskall');
	}
	
	public function download()
	{
	    return View::fetch('download');
	}
   
}
