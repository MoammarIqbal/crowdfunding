<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Domains\Tenancy\Services\TenantContext;
use App\Domains\Tenancy\Services\TenantDatabaseManager;
use Symfony\Component\HttpFoundation\Response;

class SwitchTenantDatabase
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected TenantDatabaseManager $databaseManager
    ) {
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

        $databaseName = $tenant->database_name;

        // If the database name hasn't been set yet on the model but the tenant
        // is active, fallback to generating it based on subdomain as a safety measure.
        if (! $databaseName) {
            $databaseName = $this->databaseManager->databaseNameForSubdomain($tenant->subdomain);
        }

        $this->databaseManager->configureTenantConnection($databaseName);

        $response = $next($request);

        // After the request is handled, purge the connection to prevent leakage
        $this->databaseManager->purgeTenantConnection();

        return $response;
    }
}
