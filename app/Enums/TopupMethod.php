<?php

namespace App\Enums;

enum TopupMethod: int
{
    use DatabaseEnumTrait;

    case CASH = 0;
    case CREDIT = 1;

}

