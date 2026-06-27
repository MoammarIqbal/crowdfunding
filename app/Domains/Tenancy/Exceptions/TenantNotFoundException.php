<?php

namespace App\Domains\Tenancy\Exceptions;

use Exception;

class TenantNotFoundException extends Exception
{
    public function __construct(string $identifier)
    {
        parent::__construct("The requested tenant could not be found based on the provided identifier: {$identifier}.");
    }
}
