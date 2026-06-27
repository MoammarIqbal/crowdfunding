<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Domains\Tenancy\Services\TenantContext;
use App\Domains\Tenancy\Exceptions\TenantInactiveException;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function __construct(protected TenantContext $tenantContext)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tenantContext->has()) {
            return $next($request);
        }

        $tenant = $this->tenantContext->require();

        if (! $tenant->isActive()) {
            throw new TenantInactiveException($tenant->status);
        }

        return $next($request);
    }
}
