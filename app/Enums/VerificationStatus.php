<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case PENDING = 'Pending';
    case VERIFIED = 'Verified';
    case REJECTED = 'Rejected';
}
