<?php

namespace App\Http\Responses\API;

use Illuminate\Http\JsonResponse;

class BaseResponse extends JsonResponse
{
    public function __construct($data = null, $message = null, $status = 200)
    {
        $response = [
            'success' => $status >= 200 && $status <= 299,
            'message' => $message,
            'data'    => $data,
        ];

        parent::__construct($response, $status);
    }
}