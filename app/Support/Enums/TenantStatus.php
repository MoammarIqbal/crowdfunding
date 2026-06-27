<?php

namespace App\Support\Enums;

enum TenantStatus: string
{
    case PENDING_REGISTRATION = 'pending_registration';
    case PENDING_PAYMENT = 'pending_payment';
    case PENDING_APPROVAL = 'pending_approval';
    case PROVISIONING = 'provisioning';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case REJECTED = 'rejected';
}
