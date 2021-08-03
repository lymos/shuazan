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
	
	public function __construct(\think\App $app)
	{
		if(php_sapi_name() !== 'cli'){
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
     * 频率：每天11点钟执行一次
     * 结算入口
     */
    public function settleMain(){
        $this->dir_flag = 'settleMain-logs';
        $this->date_filename = date('Y-m-d');
        $this->date = date('Y-m-d');
        $this->log('start');
        $user_data = $this->_userData();
		
        if(! $user_data){
            $this->log('no data');
            $this->log('end');
            exit;
        }
        $gain = [];
        foreach($user_data as $rs){
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
			
        }		

        // 更新记录表
        $status = $this->_insertGain($gain);
        if(! $status){
            $this->log('failed');
        }else{
            $this->log('success');
        }
        $this->log('end');
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
				$this->log('no capital continue');
                continue;
            }
            $data1 = [
                'userid' => $userid,
                'percent' => $rs['task_gain'] / 100,
                'type' => 0,
                'capital' => $capital['total'],
                'gain' => $capital['total'] * $rs['task_gain'] / 100,
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
                'gain' => $capital['total'] * $rs['invite_gain'] / 100,
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

    private function _getInviteUser($userid){
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
        echo $msg;
        @file_put_contents($file, $msg, LOCK_EX | FILE_APPEND);
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
