<?php

namespace Ckryo\Laravel\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'admin_users';
    protected $connection = 'mysql';

    protected $fillable = ['name', 'post', 'email', 'mobile', 'account', 'password'];

    // 组织信息
    function orgInfo () {
        // 父表: 子表关联键 -> 父键
        return $this->belongsTo(static::class, 'parent_id')->select('id', 'title', 'uri', 'icon');
    }
}