<?php
/**
 * 存放自动任务
 * 执行：php think action admin/cron/sendEmailMain(方法名)
 * 频率：每2分钟执行一次
 */
namespace app\admin\controller;
use \think\Controller;
use \think\Db;
use \think\Request;
use app\common\model\Email;

define('RUNTIME_PATH', dirname(dirname(dirname(__DIR__))) . '/runtime/');

class Cron extends Controller{

    public $del_status = false;
    public $dir_flag = '';
    public $date_filename = '';

    /**
     * 自动任务发送邮件入口
     */
    public function sendEmailMain(){
        $this->dir_flag = 'sendemailmain_logs';
        $this->date_filename = date('Y-m-d');
        $this->log('start');
        $data = $this->getEmailData();
        if(! $data){
            $this->log('no data');
            $this->log('end');
            return ;
        }
        
        // 状态改成发送中
        $ids = array_column($data, 'id');
        $where = ['id' => $ids];
        $update_data = [
            'send_status' => 1,
            'updated_time' => date('Y-m-d H:i:s')
        ];
        $update_status = Db::name('invite_email_record')
            ->where($where)
            ->update($update_data);

        if(! $update_status){
            $this->log('update status = 1 failed');
            $this->log('end');
            return ;
        }

        foreach($data as $rs){
            $ret = $this->sendEmail($rs['email'], $rs['email_title'], $rs['email_content']);            
            if($ret){
                $rs['send_status'] = 2;
                $this->log('send email success id: ' . $rs['id']);
            }else{
                $this->log('send email failed id: ' . $rs['id'] . ' email: ' . $rs['email']);
                $rs['send_status'] = 3;
            }
            if(! $this->updateEmailData($rs)){
                $this->log('update status failed id: ' . $rs['id']);
            }
        }
        $this->log('end');
        exit;
    }

    /**
     * 发送邮件
     */
    public function sendEmail($to, $subject, $content){
        $mail = new Email();
        $data = [
            'user_email' => $to,
            'theme' => $subject,
            'content' => str_replace(["\r\n", "\n"], ['<br />', '<br />'], $content)
        ];
        return $mail->sendEmail($data, 'review@esrgear.com', 'Esr16888999');
    }

    /**
     * 更新邮件记录信息
     */
    public function updateEmailData($data){
        $where = ['id' => $data['id']];
        $update_data = [
            'send_status' => $data['send_status'],
            'send_time' => $data['send_time'] + 1,
            'updated_time' => date('Y-m-d H:i:s')
        ];
        return Db::name('invite_email_record')
            ->where($where)
            ->update($update_data);
    }

    /**
     * 获取待发送邮件记录
     */
    public function getEmailData(){  
        $time = date('H:i:s');
        
        $where = 'send_status not in (1, 2) and send_time < 5 and ((sendtime_start <= "' . $time . '" and sendtime_end >= "' . $time . '") or (sendtime_start = sendtime_end))';

        $data = Db::name('invite_email_record')
                ->field('id, email, email_title, email_content, send_status, send_time')
                ->where($where)
                ->order('id desc')
                ->paginate(20);
        return $data->items();
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
