<?php

namespace App\Exceptions;

class BusinessException extends BaseException
{
    public $httpCode = 400;
    public $message = '参数错误';
    public $code = 400200;

    public function __construct(array $codeResponse, $info = '', $httpCode = 400)
    {
        [$code, $message] = $codeResponse;
        $this->code = $code;
        $this->message = $info ?: $message;
        $this->httpCode = $httpCode;

        parent::__construct();
    }
}
