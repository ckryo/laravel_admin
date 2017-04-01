<?php

namespace Ckryo\Laravel\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfoDefault extends Model
{
    protected $table = 'admin_user_defaults';
    protected $connection = 'mysql';
    public $timestamps = false;

    protected $fillable = ['user_id', 'sex', 'qq', 'wechat', 'address'];

}