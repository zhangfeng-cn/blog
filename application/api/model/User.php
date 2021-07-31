<?php

namespace app\api\model;

use think\Model;

class User extends Model
{
    // 主键
    protected $pk = 'id';
    // 表名
    protected $table = 'user';
}
