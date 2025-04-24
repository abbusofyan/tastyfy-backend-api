<?php

namespace App\Exceptions;

use App\Enums\ApiErrorCode;
use Throwable;

class TokenExpiredException extends ApiException
{
    public function __construct(string $message = 'Token has expired', Throwable $previous = null)
    {
        parent::__construct(ApiErrorCode::TOKEN_EXPIRED, $message, 401, $previous);
    }
}