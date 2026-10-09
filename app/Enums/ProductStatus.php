<?php

namespace App\Enums;

enum ProductStatus: string
{
    case DRAFT = 'Draft';
    case PENDING_APPROVAL = 'Pending Approval';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case INACTIVE = 'Inactive';
}
