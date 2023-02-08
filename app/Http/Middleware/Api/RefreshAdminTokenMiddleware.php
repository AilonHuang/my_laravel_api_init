<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class RefreshAdminTokenMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $this->checkForToken($request);

        //获取当前守护的名称
        $present_guard = Auth::getDefaultDriver();

        //获取当前token
        $token = Auth::getToken();
        //即使过期了，也能获取到token里的 载荷 信息。
        $payload = Auth::manager()->getJWTProvider()->decode($token->get());

        //如果不包含guard字段或者guard所对应的值与当前的guard守护值不相同
        //证明是不属于当前guard守护的token
        if (empty($payload['guard']) || $payload['guard'] != $present_guard) {
            throw new TokenInvalidException();
        }

        try {
            if ($this->auth->parseToken()->authenticate()) {
                return $next($request);
            }
            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        } catch (TokenExpiredException $exception) { // token 过期
            try {
                $token = $this->auth->refresh();
                Auth::onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
                //刷新了token，将token存入数据库
                $user = Auth::user();
                $user->last_token = $token;
                $user->save();
            } catch (JWTException $exception) {
                // 无法刷新令牌
                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
            }
        }
        // 在响应头中返回新的 token
        return $this->setAuthenticationHeader($next($request), $token);
    }
}
