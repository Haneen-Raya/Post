<?php

namespace App\Services;

use Illuminate\Http\JsonResponse; 

class BaseService
{
    /**
     * Return a JSON Response For Auth method with token
     *
     * @param mixed $data the data return in the response
     * @param string $message the success message
     * @param string $token the user token
     * @param int $status the HTTP Status code
     * @return \Illuminate\Http\JsonResponse The JSON response
     */
    // إزالة static
    public function apiResponse($data, $token, $message, $status): JsonResponse
    {
        $array = [
            'data' => $data,
            'message' => $message,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
        return response()->json($array, $status);
    }

    /**
     * Return a successful JSON Response
     *
     * @param mixed $data the data return in the response
     * @param string $message the success message
     * @param int $status the HTTP Status code
     * @return \Illuminate\Http\JsonResponse The JSON response
     */
    // إزالة static
    public function successResponse($data = null, $message = "Operation Done", $status = 200): JsonResponse
    {
        $array = [
            'status' => 'success',
            'data' => $data,
            'message' => trans($message)
        ];

        return response()->json($array, $status);
    }

    /**
     * Return a Error JSON Response
     *
     * @param mixed $data the data return in the response (errors or null)
     * @param string $message the error message
     * @param int $status the HTTP Status code
     * @return \Illuminate\Http\JsonResponse The JSON response
     */
    // إزالة static
    public function errorResponse($message = "Operation Faild", $status, $data = null): JsonResponse
    {
        $array = [
            'status' => 'error',
            'data' => $data,
            'message' => trans($message)
        ];
        return response()->json($array, $status);
    }

    /**
     * Return a paginated JSON Response
     *
     * @param $data the data that will be paginated (usually an AbstractPaginator instance or ResourceCollection)
     * @param string $message the success message
     * @param int $status the HTTP Status code
     * @return \Illuminate\Http\JsonResponse The JSON response
     */
    public function resourcePaginated($data, $message = 'Operation Success', $status = 200): JsonResponse
    {
        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection && $data->resource instanceof \Illuminate\Pagination\AbstractPaginator) {
            $paginator = $data->resource;
            $resourceData = $data->collection;
        } elseif ($data instanceof \Illuminate\Pagination\AbstractPaginator) {
             $paginator = $data;
             $resourceData = $data->items();
        } else {
             throw new \InvalidArgumentException('Data passed to resourcePaginated must be an instance of ResourceCollection wrapping a Paginator, or a Paginator instance.');
        }


        $array = [
            'status' => 'success',
            'message' => trans($message),
            'data' => $resourceData,
            'pagination' => [
                'total'        => $paginator->total(),
                'count'        => $paginator->count(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages'  => $paginator->lastPage(),
            ],
        ];
        return response()->json($array, $status);
    }
}
