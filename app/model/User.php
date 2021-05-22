<?php
namespace app\model;

use think\Model;

class User extends Model{

    public static function getList(){
        $list = User::where('id', '>', 0)
            ->select();
        return $list;
    }
}
