<?php

namespace App\Api\Helpers;

use App\Exceptions\BusinessException;
use Cassandra\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected int $httpCode = Response::HTTP_OK;

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function setHttpCode($httpCode): static
    {
        $this->httpCode = $httpCode;
        return $this;
    }

    public function message($message, $status = "success"){
        return $this->status($status,[
            'message' => $message
        ]);
    }

    /**
     * 成功
     * @param  null  $data
     * @param  array  $codeResponse
     * @param  string  $status
     * @return JsonResponse
     */
    public function success($data = null, array $codeResponse=ResponseEnum::HTTP_OK, $status = "success"): JsonResponse
    {
        return $this->jsonResponse($status, $codeResponse, $data, null);
    }

    /**
     * 失败
     * @param  array  $codeResponse
     * @param  int  $httpCode
     * @param  string  $status
     * @return JsonResponse
     */
    public function fail(array $codeResponse=ResponseEnum::HTTP_ERROR, $httpCode = 400, $status = 'fail'): JsonResponse
    {
        return $this->setHttpCode($httpCode)->jsonResponse($status, $codeResponse);
    }

    /**
     * json响应
     * @param $status
     * @param $codeResponse
     * @param $data
     * @param $error
     * @return JsonResponse
     */
    private function jsonResponse($status, $codeResponse, $data = null, $error = null): JsonResponse
    {
        list($code, $message) = $codeResponse;
        $data = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'data'    => $data ?? null,
            'error'  => $error,
        ];
        if (empty($data['data'])) {
            unset($data['data']);
        }
        if (empty($data['error'])) {
            unset($data['error']);
        }
        return $this->response($data);
    }

    public function response($data, $headers = []): JsonResponse
    {
        return response()->json($data, $this->getHttpCode(), $headers);
    }

    /**
     * 成功分页返回
     * @param $page
     * @return JsonResponse
     */
    protected function successPaginate($page): JsonResponse
    {
        return $this->success($this->paginate($page));
    }

    private function paginate($page)
    {
        if ($page instanceof LengthAwarePaginator || $page instanceof JsonResource){
            return [
                'total'  => $page->total(),
                'page'   => $page->currentPage(),
                'limit'  => $page->perPage(),
                'pages'  => $page->lastPage(),
                'list'   => $page->items()
            ];
        }
        if ($page instanceof Collection){
            $page = $page->toArray();
        }
        if (!is_array($page)){
            return $page;
        }
        $total = count($page);
        return [
            'total'  => $total, //数据总数
            'page'   => 1, // 当前页码
            'limit'  => $total, // 每页的数据条数
            'pages'  => 1, // 最后一页的页码
            'list'   => $page // 数据
        ];
    }

    /**
     * 业务异常返回
     * @param  array  $codeResponse
     * @param  string  $info
     * @param  int  $httpCode
     * @throws BusinessException
     */
    public function throwBusinessException(array $codeResponse=ResponseEnum::HTTP_ERROR, string $info = '', int $httpCode = 400)
    {
        throw new BusinessException($codeResponse, $info, $httpCode);
    }
}
