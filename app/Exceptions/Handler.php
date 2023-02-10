<?php

namespace App\Exceptions;

use App\Api\Helpers\ApiResponse;
use App\Api\Helpers\ExceptionReport;
use App\Api\Helpers\ResponseEnum;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {

        });

        if (request()->is('api/*')) {
            $this->renderable(function (Throwable $exception, $request) {
                if (!env('APP_DEBUG')) {
                    // 非调试模式
                    // 将方法拦截到自己的ExceptionReport
                    $reporter = ExceptionReport::make($request, $exception);
                    if ($reporter->shouldReturn()) {
                        return $reporter->report();
                    }
                    return $reporter->prodReport();
                }
            });

            $this->renderable(function (BusinessException $exception) {
                return response()->json([
                    'status'      => 'fail',
                    'code'        => $exception->getCode(),
                    'message'     => $exception->getMessage(),
                    'request_url' => request()->url()//返回客户端当前请求的url路径
                ], $exception->httpCode);
            });

            $this->renderable(function (ValidationException $exception) {
                $errors = implode('; ', $exception->validator->errors()->all());
                return $this->throwBusinessException(ResponseEnum::CLIENT_PARAMETER_ERROR, $errors, 422);
            });
        }
    }
}
