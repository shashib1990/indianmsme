<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case ADC = 'adc';
    case DDC = 'ddc';
    case SELLER = 'seller';
}
