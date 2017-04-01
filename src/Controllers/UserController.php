<?php

namespace Ckryo\Laravel\Admin\Controllers;

use App\Http\Controllers\Controller;
use Ckryo\Laravel\Admin\Models\User;
use Ckryo\Laravel\Admin\Models\UserInfoDefault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    function __construct()
    {
        $this->middleware('auth:abc.admin,ansdk')->only('index');

        $this->middleware('auth:edf')->only('store');
    }

    // 获取所有用户信息
    function index() {
        dd('用户页面');
    }


    // 获取创建资源
    function create() {
        dd('用户页面');
    }

    // 创建用户
    function store (Request $request, User $auth) {
        $account = $request->account;
        DB::transaction(function () use ($request, $auth, $account) {
            $user = User::create([
                'name' => $request->name,
                'post' => $request->post,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'account' => $request->account,
                'password' => bcrypt($request->password)
            ]);
            $info = UserInfoDefault::create([
                'user_id' => $user->id,
                'sex' => $request->sex,
                'qq' => $request->qq,
                'wechat' => $request->wechat,
                'address' => $request->address
            ]);
        });
    }

}