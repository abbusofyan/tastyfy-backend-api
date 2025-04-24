<?php

namespace App\Enums;

enum ApiErrorCode: int
{
    // HTTP Status Codes as Base
    case SERVER_ERROR = 500;
    case UNAUTHORIZED = 401;
    case BAD_REQUEST = 400;

    // More Specific Error Codes (within 4xx range for client errors)
    case INVALID_TOKEN = 498;
    case TOKEN_EXPIRED = 499;
    case TOKEN_MISSING = 497;
    case TOKEN_STOLEN = 496;
    case TOKEN_TYPE = 495;

    case USER_NOT_FOUND = 404;
    case REGISTRATION_ERROR = 423; // Unprocessable Entity
    case VALIDATION_ERROR = 422;
    case TRANSACTION_ERROR = 429;

    public function getDescription(): string
    {
        return match ($this) {
            self::SERVER_ERROR => 'General server error.',
            self::INVALID_TOKEN => 'Invalid token.',
            self::TOKEN_EXPIRED => 'Token has expired.',
            self::TOKEN_MISSING => 'Token is missing.',
            self::TOKEN_STOLEN => 'Token has been reported as stolen.',
            self::TOKEN_TYPE => 'Invalid token type.',
            self::USER_NOT_FOUND => 'User not found.',
            self::UNAUTHORIZED => 'Unauthorized access.',
            self::BAD_REQUEST => 'Bad request.',
            self::REGISTRATION_ERROR => 'Error during registration.',
            self::VALIDATION_ERROR => 'Validation error.',
            self::TRANSACTION_ERROR => 'Transaction error.',
        };
    }
}
