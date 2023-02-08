<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AdminRequest;
use App\Http\Resources\Api\AdminResource;
use App\Http\Resources\Api\UserResource;
use App\Jobs\Api\SaveLastTokenJob;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AdminController extends BaseController
{
    public function index()
    {
        $admins = Admin::paginate(3);
        return AdminResource::collection($admins);
    }

    public function show(Admin $admin)
    {
        return $this->success(new AdminResource($admin));
    }

    //用户注册
    public function store(AdminRequest $request)
    {
        Admin::create($request->all());
        return $this->setStatusCode(201)->success('用户注册成功');
    }

    //用户登录
    public function login(Request $request)
    {
        //获取当前守护的名称
        $present_guard =Auth::getDefaultDriver();

        $name = $request->name;
        $email = $request->email;

        $data = [
            'password' => $request->password
        ];
        if ($name) {
            $data['name'] = $name;
        }
        if ($email) {
            $data['email'] = $email;
        }

        $token = Auth::claims(['guard' => $present_guard])->attempt($data);

        if ($token) {
            //如果登陆，先检查原先是否有存token，有的话先失效，然后再存入最新的token
            $user = Auth::user();
            if ($user->last_token) {
                try{
                    Auth::setToken($user->last_token)->invalidate();
                }catch (TokenExpiredException $e){
                    //因为让一个过期的token再失效，会抛出异常，所以我们捕捉异常，不需要做任何处理
                }
            }
            SaveLastTokenJob::dispatch($user,$token);

            return $this->setStatusCode(201)->success(['token' => 'bearer ' . $token]);
        }
        return $this->failed('账号或密码错误', 401);
    }

    public function logout()
    {
        Auth::logout();
        return $this->success('退出成功...');
    }

    public function info(){
        $admin = Auth::user();
        if ($admin) {
            return $this->success(new UserResource($admin));
        }
        throw new UnauthorizedHttpException('jwt-auth', '未登录');;
    }
}
