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
	 * add card
	 */
	public function addCard(){
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
}
