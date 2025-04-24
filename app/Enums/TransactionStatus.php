<?php

namespace App\Enums;

enum TransactionStatus: int
{
    use DatabaseEnumTrait;

    case  FAILED = 0;
    case SUCCESS = 1;
    case PENDING = 2;
    case CANCELLED = 3;

    public static function fromLabel(string $value): self
    {
        return match (strtolower($value)) {
            'failed' => self::FAILED,
            'success' => self::SUCCESS,
            'pending' => self::PENDING,
            'cancelled' => self::CANCELLED,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::FAILED => __('Failed'),
            self::SUCCESS => __('Success'),
            self::PENDING => __('Pending'),
            self::CANCELLED => __('Cancelled'),
        };
    }
}
