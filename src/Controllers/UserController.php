<?php

namespace Ckryo\Laravel\Admin\Controllers;

use App\Http\Controllers\Controller;
use Ckryo\Laravel\Admin\Auth;
use Ckryo\Laravel\Admin\Models\Role;
use Ckryo\Laravel\Admin\Models\User;
use Ckryo\Laravel\Admin\Models\UserInfoDefault;
use Ckryo\Laravel\Http\Facades\Logi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    function __construct()
    {
    }

    // 获取所有用户信息
    function index(Auth $auth) {
        $user = $auth->user();
        $keywords = '%'.request('keywords').'%';

        $users = $user->users()->where(function ($query) {
            $role_id = intval(request('role_id', -1));
            if ($role_id > 0) $query->where('role_id', $role_id);
        })->where(function ($query) use ($keywords) {
            $query->where('name', 'like', $keywords)
                ->orWhere('email', 'like', $keywords)
                ->orWhere('mobile', 'like', $keywords)
                ->orWhere('account', 'like', $keywords);
        })->with(['role', 'userInfo'])->paginate(10);
        return response()->page($users);
    }


    // 获取创建资源
    function create(Auth $auth) {
        $user = $auth->user();
        $org_id = $user->org_id;
        $roles = Role::where('org_id', $org_id)->get();
        return response()->ok('数据获取成功', [
            'roles' => $roles,
            'accountPrefix' => $auth->user()->account
        ]);
    }

    // 创建用户
    function store (Request $request, Auth $auth) {

        $admin = $auth->user();

        $this->validate($request, [
            'name' => 'required',
            'role_id' => [
                'required',
                Rule::exists('admin_roles', 'id')->where(function ($query) use ($admin) {
                    $query->where('org_id', $admin->org_id);
                })
            ],
            'account' => 'required|admin_account',
            'password' => 'required|between:6,16',
        ], [
            'name.required' => '姓名不能为空',
            'role_id.*' => '角色不能为空',

            'account.required' => '账号不能为空',
            'account.admin_account' => '账号已存在',
            'password.required' => '密码不能为空',
            'password.digits_between' => '密码必须是6-16位的数字、字符或符号'
        ]);

        $account = $admin->account . '@' . $request->account;
        DB::transaction(function () use ($request, $admin, $account) {
            $user = User::create([
                'name' => $request->name,
                'avatar' => $request->avatar,
                'role_id' => $request->role_id,
                'org_id' => $admin->org_id,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'account' => $account,
                'password' => bcrypt($request->password)
            ]);
            $user->userInfo()->create([
                'sex' => $request->get('sex', 0),
                'qq' => $request->qq,
                'wechat' => $request->wechat,
                'address' => $request->address,
                'birthday' => $request->birthday ? date('Y-m-d', strtotime($request->birthday)) : null
            ]);
            Logi::action($admin->id, 'admin_user', $user->id, 'create', '创建了用户:'.$user->name, json_encode($request->all(), JSON_UNESCAPED_UNICODE));
        });
        return response()->ok('用户创建成功');
    }

    function update(Request $request, Auth $auth, $user_id) {
        $admin = $auth->user();

        $this->validate($request, [
            'role_id' => [
                Rule::exists('admin_roles', 'id')->where(function ($query) use ($admin) {
                    $query->where('org_id', $admin->org_id);
                })
            ],
            'account' => 'admin_account',
            'password' => 'between:6,16',
        ], [
            'role_id.*' => '角色不能为空',

            'account.admin_account' => '账号已存在',
            'password.digits_between' => '密码必须是6-16位的数字、字符或符号'
        ]);

        $updates = [];
        foreach ($request->only(['name', 'avatar', 'role_id', 'org_id', 'email', 'mobile', 'account', 'password', 'sex', 'qq', 'wechat', 'address', 'birthday']) as $key => $value) {
            if ($value) $updates[$key] = $value;
        }

        if (count($updates) === 0) {
            return response()->ok('未修改任何数据');
        }

        DB::transaction(function () use ($updates, $admin, $user_id) {
            $user = User::find($user_id);
            if (!$user) throw new \Exception('非法操作,角色不存在');
            $users = array_only($updates, ['name', 'avatar', 'role_id', 'org_id', 'email', 'mobile', 'account', 'password']);
            foreach ($users as $key => $value) {
                if ($key == 'account') {
                    $user->account = $user->org->account . '@' . $value;
                } else if ($key == 'password') {
                    $user->$key = bcrypt($value);
                } else {
                    $user->$key = $value;
                }
            }
            $userInfos = array_only($updates, ['sex', 'qq', 'wechat', 'address', 'birthday']);
            foreach ($userInfos as $key => $value) {
                if ($key == 'birthday') {
                    $user->userInfo->birthday = $value ? date('Y-m-d', strtotime($value)) : null;
                } else {
                    $user->userInfo->$key = $value;
                }
            }
            $user->userInfo->save();
            $user->save();
            Logi::action($admin->id, 'admin_user', $user_id, 'update', '修改了角色信息:'.$user->name, json_encode($updates, JSON_UNESCAPED_UNICODE));
        });
        return response()->ok('数据修改成功');
    }

}