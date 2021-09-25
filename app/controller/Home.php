<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User; 
use think\facade\Request;
use think\facade\Db;
use think\response\Json;

class Home extends BaseController
{
	public $app_android_ver = '1.01';
	
	public function __construct(\think\App $app){
		parent::__construct($app, false);
	}
	
	public function getAndroidAppVer(){
		$ret = [
			'code' => 1,
			'data' => $this->app_android_ver,
			'msg' => ''
		];
		return json($ret);
	}
	
    public function index()
    {
        return View::fetch('index');
    }

    public function login()
    {
        return View::fetch('login');
    }
	
	public function privacy()
	{
	    return View::fetch('privacy');
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
		$this->_getWxOpenid();
		$this->_setWxOpenid();
        return View::fetch('shop');
    }
	
	private function _setWxOpenid(){
		if(! isset($_GET['code'])){
			return ;
		}
		if(cookie('openid')){
			return ;
		}
		
		$wxpay_path = BASE_PATH . '/extend/WechatPay/';
		require $wxpay_path . 'config.php';
		$appid = $wxpay_config['appid'];
		$secret = $wxpay_config['app_secret'];
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $secret . '&code=' . $_GET['code'] . '&grant_type=authorization_code';
		$res = file_get_contents($url);
		$data = json_decode($res, true);
		if(isset($data['openid'])){
			$openid = $data['openid'];
			cookie('openid', $openid);
		}
	}
	
	private function _getWxOpenid(){
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false){
			return ;
		}
		if(cookie('openid')){
			return ;
		}
		$wxpay_path = BASE_PATH . '/extend/WechatPay/';
		require $wxpay_path . 'config.php';
		$appid = $wxpay_config['appid'];
		if(! isset($_GET['code'])){
			$redirect_uri = urlencode($this->url('/Home/shop'));
			$oauth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_base&state=' . rand(1, 127) . '#wechat_redirect';
			
			// header('user-agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_1 like Mac OS X) AppleWebKit/603.1.3 (KHTML, like Gecko) Version/10.0 Mobile/14E304 Safari/602.1 wechatdevtools/1.05.2106300 MicroMessenger/8.0.5 Language/zh_CN webview/16325499465061409 webdebugger port/35919 token/761e0b7d33e8b0964f5d816865081995', false);
			header('Location: ' . $oauth_url, false);
			exit;
		}
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
