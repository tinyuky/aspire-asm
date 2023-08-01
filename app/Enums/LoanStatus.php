<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class LoanStatus extends Enum
{
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;
    const PAID = 3;
}
