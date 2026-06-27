<?php

namespace App\Domains\Tenancy\Services;

use App\Domains\Tenancy\Exceptions\TenantContextMissingException;
use App\Domains\Tenancy\Models\Tenant;

class TenantContext
{
    /**
     * The currently active tenant for the request lifecycle.
     */
    protected ?Tenant $tenant = null;

    /**
     * Set the current active tenant.
     */
    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the current active tenant if one is set.
     */
    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current active tenant or throw an exception if none is set.
     *
     * @throws TenantContextMissingException
     */
    public function require(): Tenant
    {
        if (! $this->tenant) {
            throw new TenantContextMissingException();
        }

        return $this->tenant;
    }

    /**
     * Determine if a tenant is currently set in the context.
     */
    public function has(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Clear the current active tenant from the context.
     */
    public function clear(): void
    {
        $this->tenant = null;
    }
}
