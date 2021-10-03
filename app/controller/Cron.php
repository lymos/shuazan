<?php
/**
 * 存放自动任务
 * 执行：php index.php cron/settleMain(方法名)
 * 
 */
namespace app\controller;

use app\BaseController;
use think\facade\View;
use app\model\User;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;

define('RUNTIME_PATH', BASE_PATH . '/runtime/');

class Cron extends BaseController{
	
	public $del_status = false;
	public $dir_flag = '';
	public $date_filename = '';
	public $date = '';
	public $is_ctrl = false;
	
	public function __construct(\think\App $app, $is_ctrl = false)
	{
		if(php_sapi_name() !== 'cli' && ! $is_ctrl){
			die();
		}
	    parent::__construct($app, false);
	}

    /**
     * 频率：每2分钟执行一次
     * 更新任务状态
     */
    public function updateTaskMain(){
        $this->dir_flag = 'updateTaskMain-logs';
        $this->date_filename = date('Y-m-d');
        $this->date = date('Y-m-d H:i:s');
        $this->log('start');
        $task_data = $this->_taskData();
        
        if(! $task_data){
            $this->log('no data');
            $this->log('end');
            exit;
        }
        $gain = [];
        foreach($task_data as $rs){
           // 更新记录表
           $status = $this->_updateTask($rs['id']);
           if(! $status){
               $this->log('failed user_task_id: ' . $rs['id']);
           }else{
               $this->log('success user_task_id: ' . $rs['id']);
           }
            
        }    
       
        $this->log('end');
        exit;
    }
	
	private function _updateTask($id){
		$update_data = [
			'status' => 1,
			'updated_date' => $this->date
		];
		$where = [
			'id' => $id,
			'status' => 0
		];
		return Db::name('user_task')->where($where)->update($update_data);
		
	}

    private function _taskData(){
		$date = date('Y-m-d H:i:s', strtotime('-3 minute'));
		$old_date = date('Y-m-d H:i:s', strtotime('-2 day'));
        $where = [
            ['c.status', '=', 1],
            ['b.status', '=', 0],
			['b.added_date', '<', $date],
			['b.added_date', '>', $old_date]
        ];
        $data = Db::name('task')->alias('a')
            ->join('user_task b', 'a.id = b.taskid', 'left')
            ->join('order c', 'c.userid = b.userid', 'left')
			->field('b.id')
			->where($where)
			->select()->toArray();
		return $data;
    }

    /**
     * 频率：每天23点钟执行一次
     * 结算入口
     */
    public function settleMain($userid = 0){
		
        $this->dir_flag = 'settleMain-logs';
        $this->date_filename = date('Y-m-d');
        $this->date = date('Y-m-d');
        $this->log('start');
        if($userid){
            $user_data = [
                [
                    'id' => $userid
                ]
            ];

        }else{
            $user_data = $this->_userData();
        }
        
		
        if(! $user_data){
            $this->log('no data');
            $this->log('end');
            exit;
        }
        $gain = $all_gain = [];
        foreach($user_data as $rs){
			
			// 查询是否已经计算
			$haded = Db::name('user_gain_detail')
				->field('id')
				->where(['userid' => $rs['id'], 'gain_date' => $this->date, 'type' => 2])
				->find();
            /*
			if($haded && $haded['id']){
				$this->log('haded continue userid: ' . $rs['id']);
				continue;
			}
            */
			
            $gains = $this->_gain($rs['id']);
			if(! $gains){
				$this->log('no gain continue userid: ' . $rs['id']);
				continue;
			}
					
            /*
            $task_gain = $this->_taskGain($rs['id']);
			
            // 任务是前提
            if(! $task_gain){
                continue;
            }
            $invite_gain = $this->_inviteGain($rs['id']);
            
            $gain[$rs['id']] = [
                'task_gain' => $task_gain,
                'invite_gain' => $invite_gain
            ];
            */
            $all_gain[$rs['id']] = [
                'gain' => $gains,
                'haded' => isset($haded['id']) ? $haded['id'] : 0
            ];
			
        }		

        // 更新记录表
      //  $status = $this->_insertGain($gain);
        $status = $this->_insertGainNew($all_gain);
        if(! $status){
            $this->log('failed');
        }else{
            $this->log('success');
        }
        $this->log('end');
		if($this->is_ctrl){
			return true;
		}
        exit;
    }
	
	private function _userData(){
		$data = Db::name('user')
			->field('id')
			->where(['status' => 1])
			->select()->toArray();
		return $data;
	}

    private function _insertGain($gain){
        $date = date('Y-m-d H:i:s');
        foreach($gain as $userid => $rs){
            $capital = Db::name('order')
			->field('total')
			->where(['userid' => $userid, 'status' => 1, 'total' => Config::get('app.p_price')])
            ->find();
			
            if(! $capital){
                // log
			//	$this->log('no capital continue');
            //    continue;
            }
			
            $data1 = [
                'userid' => $userid,
                'percent' => $rs['task_gain'] / 100,
                'type' => 0,
                'capital' => $capital['total'],
                'gain' => round($capital['total'] * $rs['task_gain'], 4) / 100,
                'gain_date' => $this->date,
                'added_date' => $date
            ];
			
            Db::startTrans();
            $status1 = Db::name('user_gain')
                ->insert($data1);
            if($status1 === false){
                $this->log('insert task gain error');
                Db::rollback();
                return false;
            }
            $data2 = [
                'userid' => $userid,
                'percent' => $rs['invite_gain'] / 100,
                'type' => 1,
                'capital' => $capital['total'],
                'gain' => round($capital['total'] * $rs['invite_gain'], 4) / 100,
                'gain_date' => $this->date,
                'added_date' => $date
            ];
			
            $status2 = Db::name('user_gain')
                ->insert($data2);
            if($status2 === false){
                $this->log('insert invite gain error');
                Db::rollback();
                return false;
            }
			Db::commit();
        }
        return true;
    }

    private function _insertGainNew($gain){
        $date = date('Y-m-d H:i:s');
        foreach($gain as $userid => $rs){
            $capital = Db::name('order')
            ->field('sum(total) as total')
            ->where(['userid' => $userid, 'status' => 1, 'total' => Config::get('app.p_price')])
            ->find();
            
            if(! $capital || ! $capital['total']){
				$capital['total'] = 0;
                // log
               // $this->log('no capital continue');
               // continue;
            }
            $data1 = [
                'userid' => $userid,
                'type' => 2,
                'capital' => $capital['total'],
                'gain' => $rs['gain'],
                'gain_date' => $this->date,
                'added_date' => $date
            ];
            Db::startTrans();

            if($rs['haded']){
                unset($data1['added_date']);
                $data1['updated_date'] = $date;
                $status1_update = Db::name('user_gain_detail')
                    ->where(['id' => intval($rs['haded'])])
                ->update($data1);
                if($status1_update === false){
                    $this->log('update task gain detail error');
                    Db::rollback();
                    return false;
                }
            }else{
                $status1 = Db::name('user_gain_detail')
                ->insert($data1);
                if($status1 === false){
                    $this->log('insert task gain detail error');
                    Db::rollback();
                    return false;
                }
            }
            
			/*
            $old = Db::name('user_gain') 
                ->field('gain')
                ->where(['userid' => $userid])
                ->find();
            $new_gain = $rs['gain'];
            if($old){
                $new_gain += $old['gain'];
                $data2 = [
                    'gain' => $new_gain,
                    'updated_date' => $date
                ];
                $status2 = Db::name('user_gain')
                    ->where(['userid' => $userid])
                    ->update($data2);
                if($status2 === false){
                    $this->log('update gain error');
                    Db::rollback();
                    return false;
                }
            }else{
                $data2 = [
                    'userid' => $userid,
                    'gain' => $new_gain,
                    'added_date' => $date
                ];
                $status2 = Db::name('user_gain')
                    ->insert($data2);
                if($status2 === false){
                    $this->log('insert gain error');
                    Db::rollback();
                    return false;
                }
            }
			*/
		   
            Db::commit();
        }
        return true;
    }

    public function getOrderTimes($userid){
        $where = [
            'userid' => $userid,
            'status' => 1
        ];
        $count = Db::name('order')
            ->field('count(*) as count')
            ->where($where)
            ->find();
        return $count['count'];
    }

    /**
     * @param {Object} $userid
     * rate %
     */
    private function _gain($userid){
		$base_task_gain = 0;
		// order times
		$order_times = $this->getOrderTimes($userid);
		if($order_times){
			$base_task_gain = 36;
		}
		
        $where = [
            'userid' => $userid,
            'date' => $this->date,
            'process' => 100,
            'status' => 1
        ];
        $list = Db::name('user_task')
            ->field('id')
            ->where($where)
            ->select()->toArray();
        if(! $list){
            $base_task_gain = 0;
        }
        $count = count($list);
        // 满2个才给
        if($count < 2){
            $base_task_gain = 0;
        }else if($order_times){
			$base_task_gain = 36;
		}
		
        $level_data = $this->getInviteSum($userid);
        $n = $level_data['count'];
        $sum = 0;
        switch($level_data['level']){
            case 1:
                $sum = $base_task_gain + (18 * $n);
            break;
            case 2:
                $sum = $base_task_gain + ((18 + 9) * $n);
            break;
            case 3:
                $sum = $base_task_gain + ((18 + 18) * $n);
            break;
            case 4:
                $sum = $base_task_gain + ((18 + 18 + 9) * $n);
            break;
            case 5:
                $sum = $base_task_gain + ((18 + 18 + 18) * $n);
            break;
        }
        $gain = round($sum, 2);
		if($order_times){
			$gain = $gain * $order_times;
		}
        return $gain;
    }

	/**
	 * @param {Object} $userid
	 * rate %
	 */
    private function _taskGain($userid){
        $rate = 0;
        $where = [
			'userid' => $userid,
            'date' => $this->date,
            'process' => 100,
            'status' => 1
		];
		$list = Db::name('user_task')
			->field('id')
			->where($where)
            ->select()->toArray();
		
        if(! $list){
            return false;
        }
        $count = count($list);
        // 满2个才给
        if($count < 2){
            return false;
        }
        $rate = 1;

        // 算邀请
        $invite_data = $this->_getInviteUser($userid);
		
        $count = $invite_data['count'];
        if($count >= 5 && $count < 15){
            $rate = 2; 
        }else if($count >= 15 && $count < 50){
            $rate = 4;
        }else if($count >= 50 && $count < 100){
            $rate = 10;
        }else if($count >= 100){
            $rate = 30;
        }
        return $rate;
    }

    /**
    * 1 <= 10
    * 2 11,50
    * 3 51, 100
    * 4 101, 500  2ge2 > 25
    * 5 501   1ge3 & 2ge2
    */
    public function getInviteSum($userid){
        $invite_data = $this->_getInviteUser($userid);
        $count1 = $invite_data['count'];
        $count2 = 0;
        $level = 1;
        if($invite_data['list']){
            foreach($invite_data['list'] as $rs){
                $temp = $this->_getInviteUser($rs['invite_userid']);
            
                if(! $temp || ! $temp['count']){
                    continue;
                }
                $count2 += $temp['count'];
                $list = $temp['list'];
            }       
        }
        $sum = $count1 + $count2 + 1; // 加上自己
        if($sum > 10 && $sum < 51){
            $level = 2;
        }else if($sum > 50 && $sum < 101){
            $level = 3;
        }else if($sum > 100 && $sum < 501){
            $level = 4;
            // 2ge2 > 25

        }else if($sum > 500){
            $level = 5;
            // 1ge3 2ge2
        }

        return [
            'count' => $sum - 1, // 减去自己才是邀请人数
            'level' => $level
        ];
    }

    private function _getInviteUser($userid){
	/*
        $where = [
			'userid' => $userid,
            'status' => 1
		];
		$list = Db::name('user_invite')
			->field('invite_userid')
			->where($where)
            ->select()->toArray();
        $count = count($list);
        return [
            'count' => $count,
            'list' => $list
        ];
	*/
	$where = [
			'a.userid' => $userid,
            'a.status' => 1,
            'b.status' => 1
		];
		$list = Db::name('user_invite')->alias('a')
            ->join('order b', 'a.invite_userid = b.userid', 'left')
			->field('distinct a.invite_userid')
			->where($where)
            ->select()->toArray();
        $count = count($list);
        return [
            'count' => $count,
            'list' => $list
        ];
    }

    private function _inviteGain($userid){
        $rate = 0;
        $invite_data = $this->_getInviteUser($userid);
		
        if(! $invite_data || ! $invite_data['count']){
            return $rate;
        }
        $rate = 0.5 * $invite_data['count'];

        // 算二级
        foreach($invite_data['list'] as $rs){
            $temp = $this->_getInviteUser($rs['invite_userid']);
			
            if(! $temp || ! $temp['count']){
                continue;
            }
            $rate += 0.25 * $temp['count'];
        }		
        return $rate;
    }


    /**
     * 记录日志
     * @param $msg 
     */
    public function log($msg){

        $dir = RUNTIME_PATH . 'cron-logs';
        if(! file_exists($dir)){
            mkdir($dir, 0777);
        }
        $dir .= '/' . $this->dir_flag;
        if(! file_exists($dir)){
            mkdir($dir, 0777);
        }
        // 删除旧文件
        if(! $this->del_status){
            $this->delfile($dir);
        }

        $msg = date('Y-m-d H:i:s') . '======' . $msg . "<br />\r\n";
        $file = $dir . '/' . $this->date_filename . '.log';
		if(! $this->is_ctrl){
			echo $msg;
		}
        
        file_put_contents($file, $msg, LOCK_EX | FILE_APPEND);
    }

    /**
     * 删除文件
     */
    public function delfile($dir, $n = 7){
        $this->del_status = true;
        if(! is_dir($dir)){
            return ;
        }
        $dh = opendir($dir);
        if(! $dh){
            return ;
        }
        while(false !== ($file = readdir($dh))){
            if($file != '.' && $file != '..'){
                $fullpath = $dir . '/' . $file;
                if(! is_dir($fullpath)){
                    $info = pathinfo($fullpath);
                    if(! isset($info['extension']) || $info['extension'] != 'log'){
                        continue;
                    }
                    $filedate = date('Y-m-d', filemtime($fullpath));
                    $d1 = strtotime(date('Y-m-d'));
                    $d2 = strtotime($filedate);
                    $diff_days = round(($d1 - $d2) / 3600 / 24);
                    if($diff_days > $n){
                        unlink($fullpath);
                    }
                }
            }
        }
        closedir($dh);
    }
}
