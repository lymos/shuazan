<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

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
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }
	
	public function url($uri){
		$host = $_SERVER['HTTP_HOST'];
		$scheme = $_SERVER['REQUEST_SCHEME'];
		$url = $scheme . '://' . $host . $uri;
		return $url;
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

	public function verify_token(){
		return true;
		return false;
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
}
