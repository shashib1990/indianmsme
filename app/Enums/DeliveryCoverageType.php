<?php

namespace App\Enums;

enum DeliveryCoverageType: string
{
    case PAN_INDIA = 'pan_india';
    case STATE = 'state';
    case DISTRICT = 'district';
    case PINCODE = 'pincode';
}
