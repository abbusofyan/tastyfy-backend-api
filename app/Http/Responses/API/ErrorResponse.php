<?php

namespace App\Http\Responses\API;

use App\Enums\ApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Error Response",
 *     description="Standard error response format",
 *     type="object",
 *     required={"error"},
 *     properties={
 *         @OA\Property(
 *             property="error",
 *             type="object",
 *             required={"code", "message", "status"},
 *             properties={
 *                 @OA\Property(
 *                     property="code",
 *                     type="string",
 *                     description="Error code from the ApiErrorCode enum",
 *                     enum={"SERVER_ERROR", "UNAUTHORIZED", "BAD_REQUEST", "INVALID_TOKEN", "TOKEN_EXPIRED", "TOKEN_MISSING", "TOKEN_STOLEN", "TOKEN_TYPE", "USER_NOT_FOUND", "REGISTRATION_ERROR", "VALIDATION_ERROR"}
 *                 ),
 *                 @OA\Property(property="message", type="string", description="Error message"),
 *                 @OA\Property(property="status", type="integer", description="HTTP status code")
 *             }
 *         )
 *     }
 * )
 */
class ErrorResponse extends BaseResponse
{
    protected $status;

    public function __construct(ApiErrorCode $code, string $message = null, $status = null)
    {
        $this->status = $status;

        $status ??= $code->value;
        $message ??= $code->getDescription();

        $errorData = [
            'error' => [
                'code'    => $code->name,
                'message' => $message,
                'status'  => $status,
            ],
        ];

        parent::__construct($errorData, $code->getDescription(), $status);
    }

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this->data, $this->status);
    }
}