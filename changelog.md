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

---

## 2026-06-27 — Phase 1, Task 2: Tenant Registry Foundation

### Summary

Created the foundational structures for the Tenant domain. This includes setting up the necessary Enums, Models, Exceptions, Services, and Migrations for managing the `tenants` and `tenant_domains` records within the central database. The `TenantContext` service has been implemented and bound in the service container.

### Files Created

| File | Purpose |
|---|---|
| `app/Support/Enums/TenantStatus.php` | Defines the different lifecycle statuses for a tenant (pending_registration, active, suspended, etc). |
| `app/Domains/Tenancy/Exceptions/TenantNotFoundException.php` | Thrown when a tenant cannot be found. |
| `app/Domains/Tenancy/Exceptions/TenantInactiveException.php` | Thrown when trying to access a tenant that is not in active state. |
| `app/Domains/Tenancy/Exceptions/TenantContextMissingException.php` | Thrown when trying to retrieve a tenant from the context but none is set. |
| `app/Domains/Tenancy/Models/Tenant.php` | The main Tenant model representing a multi-tenant client in the central database. |
| `app/Domains/Tenancy/Models/TenantDomain.php` | The model representing custom domains and subdomains assigned to a tenant. |
| `app/Domains/Tenancy/Services/TenantContext.php` | A service class responsible for holding the active tenant throughout the request lifecycle. |
| `database/migrations/central/2026_06_27_160518_create_tenants_table.php` | Migration for creating the `tenants` table in the central database. |
| `database/migrations/central/2026_06_27_160519_create_tenant_domains_table.php` | Migration for creating the `tenant_domains` table in the central database. |

### Files Modified

| File | Change |
|---|---|
| `app/Providers/AppServiceProvider.php` | Registered the `TenantContext` service as a singleton. |
| `DECISIONS.md` | Added notes regarding the tenant registry being stored centrally and why domains are stored centrally. |
| `docs/INFRASTRUCTURE.md` | Added a section explaining the Tenant Registry and its role in the central DB. |
| `changelog.md` | Added this entry. |

### Important Notes

1. Migrations have been created in `database/migrations/central` but **HAVE NOT BEEN RUN**. 
2. The TenantContext does not yet have any middleware to populate it.
3. Database switching is not yet implemented.

### Corrections (Post-Review)
- Removed a redundant index on `domain` in `tenant_domains` migration since `unique()` automatically applies one.
- Enforced `registration_fee_amount` in the `tenants` migration to be non-nullable with a default of `100.00000000`.
- Refactored `AppServiceProvider.php` to use a clean `use` import for the `TenantContext` binding.
- Migrations are still intentionally unrun.

### Remaining Next Task

**Phase 1, Task 3: Tenant Database Provisioning & Middleware**
Implement the tenant database creation logic and middleware to switch connections and populate the `TenantContext`.

---

## 2026-06-27 — Phase 1, Task 3: Tenant Database Provisioning & Middleware

### Summary

Implemented the core tenant resolution and database switching mechanism. The application now correctly identifies a tenant based on the requested subdomain, validates their active status, and dynamically switches the `tenant` database connection using the newly created `TenantDatabaseManager`. A placeholder migration was added to test the tenant connection separately from the central connection, and a diagnostic route was created for verification.

### Files Created

| File | Purpose |
|---|---|
| `app/Domains/Tenancy/Services/TenantDatabaseManager.php` | Handles database name generation, strict validation, safe connection switching, and database creation. |
| `app/Http/Middleware/IdentifyTenant.php` | Intercepts subdomain requests, looks up the tenant in the central DB, and populates `TenantContext`. |
| `app/Http/Middleware/EnsureTenantIsActive.php` | Guards against suspended, rejected, or pending tenants accessing their subdomain. |
| `app/Http/Middleware/SwitchTenantDatabase.php` | Invokes `TenantDatabaseManager` to switch the active `tenant` connection for the request and purge it afterward. |
| `routes/api_tenant.php` | Diagnostic routes restricted by the tenant middleware pipeline. |
| `database/migrations/tenant/2026_06_27_161830_create_tenant_settings_table.php` | Placeholder migration strictly for the tenant database to verify segregated migrations later. |

### Files Modified

| File | Change |
|---|---|
| `bootstrap/app.php` | Registered middleware aliases (`identify.tenant`, `ensure.tenant.active`, `switch.tenant.database`) and the new `api_tenant.php` routing file. |
| `changelog.md` | Added this entry. |
| `DECISIONS.md` | Documented subdomain identification, DB switching logic, connection leakage prevention, and name validation. |
| `docs/INFRASTRUCTURE.md` | Appended the tenant request flow with a visual diagram. |

### Important Notes

1. Migrations have been created in `database/migrations/tenant` but **HAVE NOT BEEN RUN**. 
2. The `api/tenant-context` route is strictly diagnostic. It leverages the TenantContext.

### Corrections (Post-Review)
- Hardened subdomain extraction in `IdentifyTenant.php` to use a suffix-based approach instead of global string replacement. Extracted subdomains are now strictly validated against safe characters (lowercase letters, numbers, and hyphens).
- No migrations were run during this correction pass.
- No git commands were run during this correction pass.

### Remaining Next Task

**Phase 1, Task 4: Tenant Registration API**
Implement the endpoint for new tenants to register, initiating the pending state and recording their subdomain claim.
