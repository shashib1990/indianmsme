<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case PENDING = 'Pending';
    case ACCEPTED = 'Accepted';
    case PROCESSING = 'Processing';
    case DISPATCHED = 'Dispatched';
    case RECEIVED = 'Received';
    case CANCELLED = 'Cancelled';
    case REJECTED = 'Rejected';
}
