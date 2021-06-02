<?php
/**
 * 存放自动任务
 * 执行：php think action admin/cron/settleMain(方法名)
 * 频率：每分钟执行一次
 */
namespace app\controller;
use \think\Controller;
use \think\Db;
use \think\Request;
use app\common\model\Email;

define('RUNTIME_PATH', dirname(dirname(dirname(__DIR__))) . '/runtime/');

class Cron extends Controller{

    public $del_status = false;
    public $dir_flag = '';
    public $date_filename = '';
    public $date = '';

    /**
     * 每天11点执行
     * 结算入口
     */
    public function settleMain(){
        $this->dir_flag = 'settleMain-logs';
        $this->date_filename = date('Y-m-d');
        $this->date = date('Y-m-d H:i:s');
        $this->log('start');
        $user_data = $this->userData();
        if(! $data){
            $this->log('no data');
            $this->log('end');
            return ;
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

    private function _insertGain($gain){
        $date = date('Y-m-d H:i:s');
        foreach($gain as $userid => $rs){
            $capital = Db::name('order')
			->field('total')
			->where(['userid' => $userid, 'status' => 1])
            ->find();
            if(! $capital){
                // log

                continue;
            }
            $data1 = [
                'userid' => $userid,
                'percent' => $rs['task_gain'],
                'type' => 0,
                'capital' => $capital['capital'],
                'gain' => $capital['capital'] * $rs['task_gain'],
                'gain_date' => $this->date,
                'added_date' => $date
            ];
            Db::startTrans();
            $status1 = Db::name('user_gain')
                ->insert($data1);
            if($status1 === false){
                $this->log('');
                Db::rollback();
                return false;
            }
            $data2 = [
                'userid' => $userid,
                'percent' => $rs['invite_gain'],
                'type' => 1,
                'capital' => $capital['capital'],
                'gain' => $capital['capital'] * $rs['invite_gain'],
                'gain_date' => $this->date,
                'added_date' => $date
            ];
            Db::startTrans();
            $status2 = Db::name('user_gain')
                ->insert($data2);
            if($status2 === false){
                $this->log('');
                Db::rollback();
                return false;
            }
        }
        return true;
    }

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
            ->select();
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
            ->select();
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

        $dir = RUNTIME_PATH . '/cron-logs';
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
