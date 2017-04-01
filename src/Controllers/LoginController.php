<?php

namespace Ckryo\Laravel\Admin\Controllers;

use App\Http\Controllers\Controller;
use Ckryo\Laravel\Admin\Auth;
use Ckryo\Laravel\Admin\Models\Menu;
use Ckryo\Laravel\Admin\Models\User;
use Ckryo\Laravel\Http\ErrorCodeException;
use Ckryo\Laravel\Http\Facades\Logi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    private $user;

    function index(Request $request, Auth $auth, $token = null) {
        $user = $auth->user();
        $res = [
            'user_info' => [],
            'org_info' => [],
            'menu' => [
                'top' => Menu::buildMenuTop($user),
                'map' => Menu::buildMenuMap($user),
            ]
        ];
        if ($token) {
            $res['api_token'] = $token;
        } else {
            $token = $request->header('api-token');
            Logi::login($user->id, $token, true);
            $res['api_token'] = $token;
        }
        return response()->ok('登录成功', $res);
    }

    function store(Request $request, Auth $auth) {
        $this->validate($request, [
            'name' => 'required',
            'password' => 'required'
        ], [
            'name.required' => '账号不能为空',
            'password.required' => '密码不能为空',
        ]);
        $name = $request->name;
        if ($user = User::where('account', $name)->first()) {
            if (!Hash::check($request->password, $user->password)) {
                throw new ErrorCodeException(211);
            }
            $this->user = $user;
            $token = $auth->login($user);
            return $this->index($request, $auth, $token);
        }
        throw new ErrorCodeException(210);
    }

}