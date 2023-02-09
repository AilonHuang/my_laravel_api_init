<?php

namespace App\Exceptions;

use App\Api\Helpers\ExceptionReport;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
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

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')){
                // 将方法拦截到自己的ExceptionReport
                $reporter = ExceptionReport::make($e);
                if ($reporter->shouldReturn()){
                    return $reporter->report();
                }

                if (!env('APP_DEBUG')) {
                    // 非调试模式
                    return $reporter->prodReport();
                }
            }
        });
    }
}
