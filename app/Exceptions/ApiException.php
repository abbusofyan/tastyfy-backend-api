<?php

namespace App\Exceptions;

use App\Enums\ApiErrorCode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiException extends Exception implements HttpExceptionInterface
{
    protected ApiErrorCode $ApiErrCode;
    protected int $status;

    public function __construct(ApiErrorCode $code, string $message = '', int $status = 500, Throwable $previous = null)
    {
        $this->ApiErrCode = $code;
        $this->status = $status;
        $this->code = $status;

        parent::__construct($message, $status, $previous);
    }

    public function render($request): JsonResponse
    {
        // This is the key adjustment to get the correct status code
        return response()->json([
            'error' => [
                'code'    => $this->ApiErrCode->name,
                'message' => $this->getMessage(),
                'status'  => $this->status,
            ]
        ], $this->status);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return []; // Return an empty array as no headers are needed
    }

    public function report(): void
    {
        $requestData = [
            'url'     => Request::fullUrl(),
            'method'  => Request::method(),
            'headers' => Request::header(),
            'data'    => Request::all(),
            'ip'      => Request::ip()
        ];

        Log::error(
            "API Exception ({$this->status}): {$this->getMessage()}",
            [
                'code'    => $this->ApiErrCode->name,
                'request' => json_encode($requestData, JSON_PRETTY_PRINT),
            ]
        );
    }
}