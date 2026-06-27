<?php

namespace App\Support\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
