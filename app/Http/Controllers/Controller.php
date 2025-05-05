<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Generate a JSON response.
     * This method formats the response data into a consistent JSON structure.
     * @param mixed $success
     * @param mixed $message
     * @param mixed $data
     * @param mixed $status
     * @return JsonResponse|mixed
     */
    protected function jsonResponse($success, $message, $data = null, $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
