<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Main Domain
    |--------------------------------------------------------------------------
    |
    | The root domain used by the main app / marketplace. Tenant subdomains
    | are resolved relative to this domain (e.g. indonesia.crowdfund.test).
    |
    */

    'main_domain' => env('MAIN_DOMAIN', 'crowdfund.test'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Settings
    |--------------------------------------------------------------------------
    |
    | prefix: Each tenant database name is formed as {prefix}{slug}.
    |         Example: tenant_indonesia, tenant_malaysia.
    |
    | auto_create: When true, tenant provisioning jobs will create
    |              the database automatically after payment approval.
    |
    */

    'database' => [
        'prefix' => env('TENANT_DB_PREFIX', 'tenant_'),
        'auto_create' => env('TENANT_DATABASE_AUTO_CREATE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Registration Fee
    |--------------------------------------------------------------------------
    |
    | The fixed fee required for tenant registration. Payment is manual
    | and must be approved by a super admin before tenant activation.
    |
    */

    'registration_fee' => [
        'amount' => env('TENANT_REGISTRATION_FEE_AMOUNT', 100),
        'currency' => env('TENANT_REGISTRATION_FEE_CURRENCY', 'USD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Currency
    |--------------------------------------------------------------------------
    |
    | The default currency used for platform-level operations and display.
    | Individual tenants and investors may use different currencies.
    |
    */

    'default_platform_currency' => env('DEFAULT_PLATFORM_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Default Allowed Issuer Countries
    |--------------------------------------------------------------------------
    |
    | Comma-separated ISO 3166-1 alpha-2 country codes. Used to seed the
    | allowed_issuer_countries table. Admin can enable/disable per country.
    |
    */

    'default_allowed_issuer_countries' => env('DEFAULT_ALLOWED_ISSUER_COUNTRIES', 'ID,MY,SG,TH,VN,PH,BN,KH,LA,MM'),

    /*
    |--------------------------------------------------------------------------
    | Migration Paths
    |--------------------------------------------------------------------------
    |
    | Separate migration directories for central and tenant databases.
    | Central migrations run on the central connection.
    | Tenant migrations run on each tenant's dedicated database.
    |
    */

    'migration_paths' => [
        'central' => database_path('migrations/central'),
        'tenant' => database_path('migrations/tenant'),
    ],

];
