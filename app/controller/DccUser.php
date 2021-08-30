<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;
 
class DccUser extends BaseController
{
    public function index()
    {
        header('HTTP/1.1 404 NOT FOUND');
        exit;
    }

	/**
	 * 访问：http://doucc.com/index.php?s=index/get_product
	 */
	public function info(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = intval($this->decrypt(trim(Request::param('userid'))));
		$token = trim(Request::param('token'));
		if(! $userid || ! $token){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$where = [
			'id' => $userid,
			'token' => $token
		];
		$data = Db::name('user')
			->field('invite_code, mobile, id')
			->where($where)->find();
		if(! $data){
			$ret['code'] = 2;
			return json($ret);
		}
		$invite_data = $this->_getInvite($userid);
		$ret = [
			'code' => 1,
			'data' => [
				'user' => $data,
				'invite_data' => $invite_data
			],
			'msg' => ''
		];
		return json($ret);
	}
	
	private function _getInviteUser($userid){
	    $where = [
			'userid' => $userid,
	        'status' => 1
		];
		$list = Db::name('user_invite')
			->field('invite_userid')
			->where($where)
	        ->select();
	    $count = count($list);
	    return [
	        'count' => $count,
	        'list' => $list
	    ];
	}
	
	private function _getInvite($userid){
		$data = [
			'count' => 0,
			'sub_count' => 0
		];
		
	    $invite_data = $this->_getInviteUser($userid);
	    if(! $invite_data || ! $invite_data['count']){
	        return $data;
	    }
		$data['count'] = $invite_data['count'];
	
	    // 算二级
	    foreach($invite_data['list'] as $rs){
	        $temp = $this->_getInviteUser($rs['invite_userid']);
	        if(! $temp || ! $temp['count']){
	            continue;
	        }
	        $data['sub_count'] += $temp['count'];
	    }
	    return $data;
	}
	
	/**
	 * 生成二维码和邀请码
	 */
	public function getInviteInfo(){
		if(! $this->verify_token()){
			return json(['code' => 0, 'data' => '', 'msg' => 'token is invaild']);
		}
		$data = Db::table('dcc_user')
			->field('invite_code, mobile, id')
			->where('id', 1)->find();
		$invite_data = $this->getUserInvite();
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}
	
	/**
	 * 获取用户收益信息
	 */
	public function getUserGain(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = $this->decrypt(trim(Request::param('userid')));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		
		// 获取本金
		$where_order = [
			'userid' => $userid,
			'status' => 1
		];
		$order = Db::name('order')
			->field('total')
			->where($where_order)
			->find();
		
		$where_task = [
			'userid' => $userid,
			'status' => 0,
			'type' => 0
		];
		$temp_task = Db::name('user_gain')
			->field('sum(gain) as task_gain')
			->where($where_task)
			->find();
			
		$where_invite = [
			'userid' => $userid,
			'status' => 0,
			'type' => 1
		];
		$temp_invite = Db::name('user_gain')
			->field('sum(gain) as invite_gain')
			->where($where_invite)
			->find();
			
		$ret['data'] = [
			'capital' => $order['total'],
			'task_gain' => $temp_task['task_gain'] ? $temp_task['task_gain'] : 0,
			'invite_gain' => $temp_invite['invite_gain'] ? $temp_invite['invite_gain'] : 0,
			'total' => $order['total'] + $temp_task['task_gain'] + $temp_invite['invite_gain']
		];
		$ret['code'] = 1;
		return json($ret);
	}
	
	/**
	 * 访问：http://doucc.com/index.php?s=DccUserr/cashout
	 * 提现
	 */
	public function cashout(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = $this->decrypt(trim(Request::param('userid')));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$capital = trim(Request::param('capital'));
		$gain = trim(Request::param('gain'));
		if(! $capital && ! $gain){
			$ret['msg'] = '请输入提现金额';
			return json($ret);
		}
		if($capital && ! is_numeric($capital)){
			$ret['msg'] = '请输入正确的金额';
			return json($ret);
		}
		if($gain && ! is_numeric($gain)){
			$ret['msg'] = '请输入正确的金额';
			return json($ret);
		}
		
		// 判断是否在可提现范围
		$this->_checkCanCashout($userid, $capital, $gain);
		$date = date('Y-m-d H:i:s');
		$data = [
			'userid' => $userid,
			'added_by' => $userid,
			'added_date' => $date
		];
		
		
		Db::startTrans();
		if($capital){
			$data['type'] = 1;
			$data['amount'] = $capital;
			$status1 = Db::name('cashout_record')
				->insert($data);
			if($status1 === false){
				Db::rollback();
				$ret['msg'] = '发生错误 code 70001';
				return json($ret);
			} 
		}
			
		if($gain){
			$data['type'] = 0;
			$data['amount'] = $gain;
			$status2 = Db::name('cashout_record')
				->insert($data);
			if($status2 === false){
				Db::rollback();
				$ret['msg'] = '发生错误 code 70002';
				return json($ret);
			} 
		}
		Db::commit();
		
		$ret = [
			'code' => 1,
			'data' => '',
			'msg' => ''
		];
		return json($ret);
	}
	
	private function _checkCanCashout($userid, $capital, $gain){
		if($capital){
			$capital_temp = Db::name('order')
            ->field('sum(total) as total')
            ->where(['userid' => $userid, 'status' => 1])
            ->find();
            if($capital > $capital_temp['total']){
            	return false;
            }
		}

		if($gain){
			$gain_temp = Db::name('user_gain')
            ->field('sum(gain) as tatal')
            ->where(['userid' => $userid, 'status' => 0, 'type' => 2])
            ->find();
            if($gain > $gain_temp['total']){
            	return false;
            }
		}
		

	}
	
	public function getCard(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = intval($this->decrypt(trim(Request::param('userid'))));
		$token = trim(Request::param('token'));
		if(! $userid || ! $token){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$where = [
			'userid' => $userid
		];
		$data = Db::name('user_card')
			->field('wx_qrcode, ali_qrcode')
			->where($where)->find();
		if($data['wx_qrcode']){
			$data['wx_qrcode'] = $this->url($data['wx_qrcode']);
		}
		if($data['ali_qrcode']){
			$data['ali_qrcode'] = $this->url($data['ali_qrcode']);
		}
		$ret = [
			'code' => 1,
			'data' => $data,
			'msg' => ''
		];
		return json($ret);
	}
	
	
	/**
	 * 获取签到数据
	 */
	public function signinInfo(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = $this->decrypt(trim(Request::param('userid')));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$where = [
			'userid' => $userid,
			'status' => 0
		];
		$data = Db::name('user_signin')
			->field('signin_date, days')
			->where($where)
			->order('signin_date', 'desc')
			->select();
			
		$date = date('Y-m-d');
		// 判断当天是否已签到
		$istoday = false;
		if(isset($data[0]) && $data[0]['signin_date'] == $date){
			$istoday = true;
		}else if(isset($data[0])){
			// 判断昨天有没签到
			$prev_date = date('Y-m-d', strtotime('-1 day'));
			if($data[0]['signin_date'] != $prev_date){
				if(! $this->_cancelSignin($userid)){
					$ret['msg'] = '系统发生错误';
					return json($ret);
				}else{
					// 重新获取
					$data = Db::name('user_signin')
						->field('signin_date, days')
						->where($where)
						->order('signin_date', 'desc')
						->select();
				}
			}
		}
		
		// 当天是否满14
		$where2 = [
			'userid' => $userid,
			'days' => 14,
			'signin_date' => $date
		];
		$data2 = Db::name('user_signin')
			->field('id')
			->where($where2)->find();
		if($data2){
			$is14 = true;
		}else{
			$is14 = false;
		}

		$ret['data'] = [
			'days' => count($data),
			'istoday' => $istoday,
			'list' => $data,
			'is14' => $is14 // 满14
		];
		$ret['code'] = 1;
		return json($ret);
	}
	
	private function _cancelSignin($userid){
		// 以前的设置为废弃
		$update_data = [
			'status' => 2,
			'updated_by' => $userid,
			'updated_date' => date('Y-m-d H:i:s')
		];
		$update_where = [
			'status' => 0,
			'userid' => $userid
		];
		$update_status = Db::name('user_signin')
			->where($update_where)->update($update_data);
		if($update_status === false){
			return false;
		}
		return true;
	}
	
	/**
	 * 签到
	 */
	public function signin(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$userid = $this->decrypt(trim(Request::param('userid')));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
	
		$date = date('Y-m-d');
		$time = date('Y-m-d H:i:s');
		
		Db::startTrans();
		// 判断是否连续 不连续需把之前的废弃
		$days = $this->_findContinue($userid, $date, $time);
		if(! $days){
			Db::rollback();
			$ret['msg'] = '发生错误';
			return json($ret);
		}

		$data = [
			'userid' => $userid,
			'signin_date' => $date,
			'days' => $days,
			'added_by' => $userid,
			'added_date' => $time
		];
		$res = Db::name('user_signin')->insert($data);
		if($res === false){
			Db::rollback();
			$ret['msg'] = '发生错误';
			return json($ret);
		}
		if($days == 14){
			// 满送1个邀请用户
			$insert_data = [
				'mobile' => uniqid(),
				'type' => 2,
				'added_date' => $time
			];
			$insert_userid = Db::name('user')->insertGetId($insert_data);
			if($insert_userid === false){
				Db::rollback();
				$ret['msg'] = '发生错误';
				return json($ret);
			}
			$invite_data = [
				'userid' => $userid,
				'invite_userid' => $insert_userid
			];
			$invite_status = Db::name('user_invite')->insertGetId($invite_data);
			if($invite_status === false){
				Db::rollback();
				$ret['msg'] = '发生错误 code 30001';
				return json($ret);
			}

			// 更新状态为结算
			$where_signin = [
				'userid' => $userid,
				'status' => 0
			];
			$update_status = Db::name('user_signin')
				->where($where_signin)
				->update([
					'status' => 1,
					'updated_by' => $userid,
					'updated_date' => $time
				]);

			if($update_status === false){
				Db::rollback();
				$ret['msg'] = '发生错误 code 30002';
				return json($ret);
			}
		}

		Db::commit();
		$ret['code'] = 1;
		return json($ret);
	}
	
	private function _findContinue($userid, $date, $time){
		$days = 0;
		$prev = date('Y-m-d', strtotime($date . ' -1 day'));
		$where = [
			['status', '=', 0],
			['userid', '=', $userid],
			['signin_date', '=', $prev]
		];
		$data = Db::name('user_signin')
			->field('id, days')
			->where($where)->find();
		if(! $data){
			$days = 1;
			// 以前的设置为废弃
			$update_data = [
				'status' => 2,
				'updated_by' => $userid,
				'updated_date' => $time
			];
			$update_where = [
				'status' => 0,
				'userid' => $userid
			];
			$update_status = Db::name('user_signin')
				->where($update_where)->update($update_data);
			if($update_status === false){
				return false;
			}
		}else{
			$days = $data['days'] + 1;
		}
		return $days;
	}


	public function addPayQrcode(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		error_log(print_r($_FILES, true) . "\r\n", 3, '/Volumes/mac-disk/work/www/debug.log');
		
		$userid = $this->decrypt(trim(Request::param('userid')));
		if(! $userid){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		
		// move file
		$wx_qrcode = '';
		$ali_qrcode = '';
		$dir = BASE_PATH . '/public/upload';
		$uri = '/upload';
		if(! file_exists($dir)){
			mkdir($dir, 0777);
		}
		$day = date('Ymd');
		$dir .= '/' . $day;
		$uri .= '/' . $day;
		if(! file_exists($dir)){
			mkdir($dir, 0777);
		}
		
		$filename = date('YmdHis') . uniqid();
		if(isset($_FILES['wx_qrcode']) && $_FILES['wx_qrcode']){
			$arr = explode('.', $_FILES['wx_qrcode']['name']);
			$type = end($arr);
			if(! $type){
				$type = 'png';
			}
			$file_status = move_uploaded_file($_FILES['wx_qrcode']['tmp_name'], $dir . '/' . $filename . '.' . $type);
			if($file_status){
				$wx_qrcode = $uri . '/' . $filename . '.' . $type;
			}
		}

		if(isset($_FILES['ali_qrcode']) && $_FILES['ali_qrcode']){
			$arr = explode('.', $_FILES['ali_qrcode']['name']);
			$type = end($arr);
			if(! $type){
				$type = 'png';
			}
			$file_status = move_uploaded_file($_FILES['ali_qrcode']['tmp_name'], $dir . '/' . $filename . '.' . $type);
			if($file_status){
				$ali_qrcode = $uri . '/' . $filename . '.' . $type;
			}
		}

		// save data
		$date = date('Y-m-d H:i:s');
		$old_id = $this->_checkQrcodeIsExists($userid);
		
		if($old_id){
			$data = [
				'updated_by' => $userid,
				'updated_date' => $date
			];
			if($wx_qrcode){
				$data['wx_qrcode'] = $wx_qrcode;
			}
			if($ali_qrcode){
				$data['ali_qrcode'] = $ali_qrcode;
			}
			$status = Db::name('user_card')->where(['id' => $old_id])->update($data);
		}else{
			$data = [
				'userid' => $userid,
				'added_by' => $userid,
				'added_date' => $date
			];
			if($wx_qrcode){
				$data['wx_qrcode'] = $wx_qrcode;
			}
			if($ali_qrcode){
				$data['ali_qrcode'] = $ali_qrcode;
			}
			
			$status = Db::name('user_card')->insert($data);
		}
		
		
		if(! $status){
			$ret['msg'] = '失败 code: 60001';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);

	}

	private function _checkQrcodeIsExists($userid){
		$data = Db::name('user_card')
			->field('id')
			->whereRaw('userid=:userid', ['userid' => $userid])
			->find();
		
		if($data){
			return $data['id'];
		}
		return null;
	}
	
	public function addCard(){
		$ret = [
			'code' => 0,
			'data' => '',
			'msg' => ''
		];
		$mobile = trim(Request::param('mobile'));
		$card = trim(Request::param('card'));
		$brand = trim(Request::param('brand'));
		$name = trim(Request::param('name'));
		$userid = $this->decrypt(trim(Request::param('userid')));
		if(! $mobile || ! $card || ! $userid || ! $name || ! $brand){
			$ret['msg'] = '参数错误';
			return json($ret);
		}
		$date = date('Y-m-d H:i:s');
	
		if($this->_checkCardIsExists($card, $userid)){
			$ret['msg'] = '您已绑定银行卡';
			return json($ret);
		}
		
		$data = [
			'userid' => $userid,
			'card' => $card,
			'name' => $name,
			'brand' => $brand,
			'added_by' => $userid,
			'mobile' => $mobile,
			'added_date' => $date
		];
		$status = Db::name('user_card')->insert($data);
		if(! $status){
			$ret['msg'] = '绑卡失败 code: 50001';
			return json($ret);
		}
		$ret['code'] = 1;
		return json($ret);
	}
	
	private function _checkCardIsExists($card, $userid){
		$data = Db::name('user_card')
			->field('id')
			->whereRaw('card = :id or userid=:userid', ['id' => $card , 'userid' => $userid])
			->find();
		
		if($data){
			return true;
		}
		return false;
	}
}
