<?php

namespace Ckryo\Laravel\Admin;

use Ckryo\Laravel\Admin\Models\User;
use Ckryo\Laravel\Http\Facades\Logi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Auth
{
    use GuardHelpers;

    protected $user;

    protected $request;

    function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;
        $token = $this->request->auth_token;

        if (! empty($token)) { // 从驱动中获取 用户信息
            $user = User::where('remember_token', $token)->first();
        }

        return $this->user = $user;
    }

    public function id()
    {
        if ($this->user()) {
            return $this->user()->id;
        }

        return null;
    }

    /**
     *
     * 登录
     *
     * @param User $user 登录用户
     * @return string 返回 token
     */
    public function login(User $user)
    {
        $time = time();
        $token = Str::random(8) . md5("admin_{$user->id}__{$time}") . Str::random(60);
        $user->remember_token = $token;
        $user->save();
        $this->user = $user;
        Logi::login($user->id, $token);
        return $token;
    }

    /**
     * Set the current request instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

}
