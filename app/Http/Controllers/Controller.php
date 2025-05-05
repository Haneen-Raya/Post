<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class Controller
{
     /**
     * @param mixed $data
     * @param string|null $token
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiResponse($data, $token, string $message, int $status): JsonResponse
    {
        $array = [
            'data' => $data,
            'message' => trans($message),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
        return response()->json($array, $status);
    }

    /**
     *
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, string $message = 'Operation successful', int $status = HttpStatus::HTTP_OK): JsonResponse
    {

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {

             return $data->additional([
                 'status' => 'success',
                 'message' => trans($message)
             ])->response()->setStatusCode($status);
        }


        return response()->json([
            'status' => 'success',
            'message' => trans($message),
            'data' => $data,
        ], $status);
    }


    /**
     *
     *
     * @param string $message
     * @param int $status
     * @param mixed|null $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse(string $message = 'Operation failed', int $status = HttpStatus::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => trans($message),
        ];


        if ($errors !== null) {
            $response['errors'] = $errors;
        } else {
            $response['data'] = null;
        }

        return response()->json($response, $status);
    }
}
