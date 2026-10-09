<?php

namespace App\Enums;

enum DispatchStatus: string
{
    case PENDING = 'Pending';
    case DISPATCHED = 'Dispatched';
    case DELIVERED = 'Delivered';
}
