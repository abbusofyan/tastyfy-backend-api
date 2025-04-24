<?php

namespace App\Enums;

enum PaymentMethod: int
{
    use DatabaseEnumTrait;

    case CASH = 0;
    case CREDIT = 1;
    case MIXED = 2;
}
