# Changelog

All notable changes to this project are documented in this file.

---

## 2026-06-27 — Phase 1, Task 1: Central + Tenant Database Configuration

### Summary

Initialized the dual-database connection architecture required for multi-tenant operation. The Laravel application now has three MySQL connections defined: the default `mysql` connection (unchanged), a `central` connection for cross-tenant data, and a `tenant` connection whose database name is dynamically set at runtime.

### Files Created

| File | Purpose |
|---|---|
| `config/tenancy.php` | Centralized tenancy configuration: main domain, DB prefix, auto-create flag, registration fee, platform currency, allowed issuer countries, and migration paths for central/tenant directories. |
| `changelog.md` | This file. Tracks implementation progress for future agents. |

### Files Modified

| File | Change |
|---|---|
| `config/database.php` | Added `central` and `tenant` MySQL connections inside the `connections` array. Existing connections (sqlite, mysql, mariadb, pgsql, sqlsrv) are untouched. |
| `.env.example` | Replaced real `APP_KEY` value with empty placeholder for safe version control. All required env vars for central/tenant connections and tenancy config were already present — no additions needed. |
| `DECISIONS.md` | Added section 19 documenting the database connection configuration decision. Cleaned trailing PowerShell artifacts from end of file. |
| `docs/INFRASTRUCTURE.md` | Added database architecture section explaining central DB, tenant DB, dynamic switching, and future middleware flow. |

### Configuration Variables Used

| Variable | Used By | Purpose |
|---|---|---|
| `CENTRAL_DB_HOST` | `config/database.php` → `central` | Central database host |
| `CENTRAL_DB_PORT` | `config/database.php` → `central` | Central database port |
| `CENTRAL_DB_DATABASE` | `config/database.php` → `central` | Central database name |
| `CENTRAL_DB_USERNAME` | `config/database.php` → `central` | Central database user |
| `CENTRAL_DB_PASSWORD` | `config/database.php` → `central` | Central database password |
| `TENANT_DB_HOST` | `config/database.php` → `tenant` | Tenant database host |
| `TENANT_DB_PORT` | `config/database.php` → `tenant` | Tenant database port |
| `TENANT_DB_USERNAME` | `config/database.php` → `tenant` | Tenant database user |
| `TENANT_DB_PASSWORD` | `config/database.php` → `tenant` | Tenant database password |
| `TENANT_DB_PREFIX` | `config/tenancy.php` | Prefix for tenant database names |
| `TENANT_DATABASE_AUTO_CREATE` | `config/tenancy.php` | Whether to auto-create tenant DBs |
| `MAIN_DOMAIN` | `config/tenancy.php` | Root domain for subdomain resolution |
| `TENANT_REGISTRATION_FEE_AMOUNT` | `config/tenancy.php` | Registration fee ($100) |
| `TENANT_REGISTRATION_FEE_CURRENCY` | `config/tenancy.php` | Registration fee currency (USD) |
| `DEFAULT_PLATFORM_CURRENCY` | `config/tenancy.php` | Default platform currency |
| `DEFAULT_ALLOWED_ISSUER_COUNTRIES` | `config/tenancy.php` | Comma-separated country codes |

### Important Notes for Next Agent

1. The `tenant` database connection has `'database' => null`. This is intentional. The `TenantDatabaseManager` service (not yet created) will set the database name at runtime after subdomain middleware identifies the active tenant.
2. No migrations were created in this task. Migration directories (`database/migrations/central/` and `database/migrations/tenant/`) will be created in a later task.
3. No packages were installed. `composer require` for Sanctum and Spatie Permission is planned for a later Phase 1 task.
4. The `.env` file was NOT modified. Only `.env.example` was updated (APP_KEY cleared for safe commit).
5. The default `mysql` connection still points to `crowdfund_central` via `DB_DATABASE`. This is fine because the default connection and the `central` connection currently target the same database.

### Remaining Next Task

**Phase 1, Task 2: Create `config/money.php` and Enum/Support Scaffolding**

Alternatively, proceed to the Tenant model and migration setup if the team prefers to establish database tables next.

Recommended next: **Phase 1, Task 2 — Tenant Model, TenantStatus Enum, and Central Migration Directory Setup** (create `database/migrations/central/` and `database/migrations/tenant/` directories, create the `tenants` table migration, and the `Tenant` model with `TenantStatus` enum).
