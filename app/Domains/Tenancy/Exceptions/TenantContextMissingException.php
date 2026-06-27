<?php

namespace App\Domains\Tenancy\Exceptions;

use Exception;

class TenantContextMissingException extends Exception
{
    public function __construct()
    {
        parent::__construct('The tenant context is missing. Ensure the request passed through the tenant identification middleware.');
    }
}
