<?php

namespace App\Traits;

trait ApiResponse
{
    // Success response
    public function sendResponse($data = [], $message, $code = 200, $token = null,$results = null)
    {
        $response = [
            'status' => true,
            'message' => __($message),
            'code' => $code,
        ];

        if ($token) {
            $response['token_type'] = 'bearer';
            $response['token'] = $token;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($results)) {
            $response['pagination'] = [
                'total'         => $results->total(),
                'current_page'  => $results->currentPage(),
                'per_page'      => $results->perPage(),
                'last_page'     => $results->lastPage(),
                'from'          => $results->firstItem(),
                'to'            => $results->lastItem(),
            ];
        }

        return response()->json($response, $code);
    }

    // Error response
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'status' => false,
            'message' => __($error),
            'code' => $code,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

}
