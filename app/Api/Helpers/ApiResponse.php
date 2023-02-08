<?php

namespace App\Api\Helpers;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Illuminate\Http\Client\Response;

trait ApiResponse
{
    protected $statusCode = FoundationResponse::HTTP_OK;

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode, $httpCode = null)
    {
        $this->statusCode = $httpCode ?? $statusCode;

        return $this;
    }

    public function respond($data, $header = [])
    {
        return response()->json($data, $this->getStatusCode(), $header);
    }

    public function status($status, array $data, $code = null)
    {
        if ($code) {
            $this->setStatusCode($code);
        }
        $status = [
            'status' => $status,
            'code'   => $this->statusCode
        ];

        $data = array_merge($status, $data);

        return $this->respond($data);
    }

    public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'error')
    {
        return $this->setStatusCode($code)->message($message, $status);
    }

    public function message($message, $status = "success")
    {
        return $this->status($status, [
            'message' => $message
        ]);
    }

    public function internalError($message = "Internal Error!")
    {
        return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function created($message = "created")
    {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)
            ->message($message);
    }

    public function success($data, $status = "success")
    {
        return $this->status($status, compact('data'));
    }

    public function notFond($message = 'Not Fond!')
    {
        return $this->failed($message, Foundationresponse::HTTP_NOT_FOUND);
    }
}
