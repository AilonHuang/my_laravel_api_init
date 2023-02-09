<?php

namespace App\Exceptions;

use \Exception;

class BaseException extends Exception
{
    // http 状态码
    public $httpCode = 400;
    // 错误消息
    public $message = '参数错误';
    // 错误码
    public $code = 400200;

    public function __construct($params = [])
    {
        if(!is_array($params)){
            return;
            //或者
//            throw new Exception('参数必须是数组');
        }

        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }
        if(array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }
        if(array_key_exists('errorCode',$params)){
            $this->errorCode = $params['errorCode'];
        }
        parent::__construct();
    }
}
