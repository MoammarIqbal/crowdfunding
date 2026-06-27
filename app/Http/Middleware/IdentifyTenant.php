<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Domains\Tenancy\Services\TenantContext;
use App\Domains\Tenancy\Models\Tenant;
use App\Domains\Tenancy\Exceptions\TenantNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class IdentifyTenant
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
        $host = strtolower($request->getHost());
        $mainDomain = strtolower(config('tenancy.main_domain'));

        // Allow main domain requests to pass through without tenant context.
        if ($host === $mainDomain) {
            return $next($request);
        }

        $suffix = '.' . $mainDomain;

        if (! Str::endsWith($host, $suffix)) {
            throw new TenantNotFoundException('invalid-domain-suffix');
        }

        $subdomain = Str::beforeLast($host, $suffix);

        if (empty($subdomain)) {
            throw new TenantNotFoundException('empty-subdomain');
        }

        if (! preg_match('/^[a-z0-9\-]+$/', $subdomain)) {
            throw new TenantNotFoundException('unsafe-subdomain');
        }

        // Find the tenant by subdomain in the central database.
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (! $tenant) {
            throw new TenantNotFoundException($subdomain);
        }

        $this->tenantContext->set($tenant);

        return $next($request);
    }
}
