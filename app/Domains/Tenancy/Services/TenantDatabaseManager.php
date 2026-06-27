<?php

namespace App\Domains\Tenancy\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class TenantDatabaseManager
{
    /**
     * Generate the fully qualified database name for a given subdomain.
     */
    public function databaseNameForSubdomain(string $subdomain): string
    {
        $prefix = Config::get('tenancy.database_prefix', 'tenant_');
        return $prefix . $subdomain;
    }

    /**
     * Validate that the database name only contains safe characters.
     */
    public function validateTenantDatabaseName(string $databaseName): void
    {
        // Only allow lowercase letters, numbers, and underscores.
        if (!preg_match('/^[a-z0-9_]+$/', $databaseName)) {
            throw new InvalidArgumentException("Unsafe or invalid tenant database name: {$databaseName}");
        }
    }

    /**
     * Configure the tenant connection to use the specified database name.
     */
    public function configureTenantConnection(string $databaseName): void
    {
        $this->validateTenantDatabaseName($databaseName);
        
        Config::set('database.connections.tenant.database', $databaseName);
        
        $this->purgeTenantConnection();
        $this->reconnectTenantConnection();
    }

    /**
     * Purge the tenant connection to clear out any cached instances.
     */
    public function purgeTenantConnection(): void
    {
        DB::purge('tenant');
    }

    /**
     * Reconnect the tenant connection.
     */
    public function reconnectTenantConnection(): void
    {
        DB::reconnect('tenant');
    }

    /**
     * Create the database if allowed by configuration.
     */
    public function createDatabaseIfAllowed(string $databaseName): void
    {
        if (!Config::get('tenancy.auto_create_database', true)) {
            return;
        }

        $this->validateTenantDatabaseName($databaseName);

        // Safely execute the CREATE DATABASE statement.
        // We run this query on the default central connection since the tenant DB doesn't exist yet.
        $safeDatabaseName = '`' . str_replace('`', '``', $databaseName) . '`';
        DB::statement("CREATE DATABASE IF NOT EXISTS {$safeDatabaseName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
}
