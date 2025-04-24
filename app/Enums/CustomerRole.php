<?php

namespace App\Enums;

enum CustomerRole: string
{
    use StringEnumTrait;

    case PUBLIC = 'Public Customer';
    case BENEFICIARIES = 'Beneficiaries Customer';
    case COPAYMENT = 'Co-Payment Customer';
}
