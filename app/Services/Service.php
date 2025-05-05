<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;

class Service
{
    /**
     * Throws an HttpResponseException with a formatted JSON error response.
     * @param mixed $message
     * @param mixed $code
     * @param mixed $errors
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return never
     */
    public function throwExceptionJson($message = 'An error occurred', $code = 500, $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        throw new HttpResponseException(response()->json($response, $code));
    }
}
