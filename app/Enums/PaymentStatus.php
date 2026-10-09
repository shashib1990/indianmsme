<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'Unpaid';
    case PAID = 'Paid';
}
