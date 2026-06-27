<?php

namespace App\Domains\Tenancy\Exceptions;

use Exception;
use App\Support\Enums\TenantStatus;

class TenantInactiveException extends Exception
{
    public function __construct(TenantStatus $status)
    {
        parent::__construct("The requested tenant is not active. Current status: {$status->value}.");
    }
}
