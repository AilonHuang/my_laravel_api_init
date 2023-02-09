<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ResponseEnum;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\Api\UserResource;
use App\Jobs\Api\SaveLastTokenJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserController extends BaseController
{
    public function index()
    {
        $users = User::paginate(3);
        return $this->successPaginate(UserResource::collection($users));
    }

    public function show(User $user)
    {
        return $this->success(new UserResource($user));
    }

    //用户注册
    public function store(UserRequest $request)
    {
        User::create($request->all());
        return $this->setHttpCode(201)->success('用户注册成功');
    }

    //用户登录
    public function login(Request $request)
    {
        //获取当前守护的名称
        $present_guard = Auth::getDefaultDriver();

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

            return $this->setHttpCode(201)->success(
                ['token' => 'bearer '.$token],
                ResponseEnum::USER_SERVICE_LOGIN_SUCCESS
            );
        }
        return $this->fail(ResponseEnum::USER_SERVICE_LOGIN_ERROR, 401);
    }

    public function logout()
    {
        Auth::logout();
        return $this->success('退出成功...');
    }

    public function info()
    {
        $user = Auth::user();
        if ($user) {
            return $this->success(new UserResource($user));
        }
        throw new UnauthorizedHttpException('jwt-auth', '未登录');;
    }
}
