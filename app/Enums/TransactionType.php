<?php

namespace App\Enums;

enum TransactionType: int
{
    use DatabaseEnumTrait;

    case TOPUP = 0;
    case PURCHASE = 1;


}
