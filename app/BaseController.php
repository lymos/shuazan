<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;
	
	protected $key = 'doucc-encrype-key';

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app, $check_token = true)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
        if($check_token){
            $this->verifyToken();
        }
    }
	
	public function url($uri){
		$host = $_SERVER['HTTP_HOST'];
		$scheme = $_SERVER['REQUEST_SCHEME'];
		$url = $scheme . '://' . $host . $uri;
		return $url;
	}
	
	public function encrypt($data, $key = '') {
		if(! $key){
			$key = $this->key;
		}
		if(is_numeric($data)){
			$data = (string)$data;
		}
	    $data = openssl_encrypt($data, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
	    return base64_encode($data);
	}
	
	public function decrypt($data, $key = '') {
		if(! $key){
			$key = $this->key;
		}
	    $encrypted = base64_decode($data);
	    return openssl_decrypt($encrypted, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
	}

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

	public function verifyToken(){
        $ret = [
            'code' => 2,
            'data' => '',
            'msg' => ''
        ];
        $token = Request::param('token');
        $token_expire = Request::param('token_expire');
        if(! $token || ! $token_expire){
            $ret['msg'] = '无效请求';
            echo json_encode($ret);
			exit;
        }
        $now = time();
        $res = Db::name('user')
            ->field('id')
            ->where([
                ['token', '=', $token],
                ['token_expire', '>', $now]
            ])->find();

        if(! $res){
            $ret['msg'] = '请先登录';
            echo json_encode($ret);
            exit;
        }
		return true;
	}
	
	public function curl($url, $method = 'GET', $data = [], $header = []){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		if(strtoupper($method) == 'POST'){
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		}

		$ret = curl_exec($ch);
		
		curl_close($ch);
		return $ret;
	}
	
	public function sendSms($mobile, $msg){
		// 判断限制
		if(! $this->_smsRule()){
			return false;
		}
		
		$url = 'http://211.149.137.53:8045/Port/default.ashx?method=SendSms&username=doucaicai&password=a123456789';
		$url .= '&phonelist=' . $mobile . '&msg=' . $msg . '&SendDatetime=';
		$ret = $this->curl($url);
		$ret = explode(',', $ret);
		if(intval($ret[0]) === 1){
			return true;
		}else{
			return false;
		}	
	}
	
	private function _smsRule(){
		// 60s
		
		// 一手机一天只能发10条
		
		// 同IP一天10条
		
		
		return true;
	}
}
