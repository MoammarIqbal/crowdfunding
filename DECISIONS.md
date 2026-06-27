# Technical Decisions

This document records the main technical decisions for the Laravel backend assessment project.

Project: White-label, multi-tenant, cross-border P2P Investment / Crowdfunding platform.

---

## 1. Architecture Decision: Modular Laravel Monolith

### Decision

The application is built as a modular Laravel monolith.

### Reason

A modular monolith is suitable for this MVP because the assessment scope is large and requires many connected business flows, including tenant management, wallet, investment, exchange rate, commission, project publishing, and scheduled unlock processing.

Using a monolith keeps development faster and easier to demonstrate, while still allowing clean separation through domain modules and service classes.

### Alternative Considered

Microservices were considered but not selected for the MVP because they would increase infrastructure complexity, deployment complexity, and cross-service transaction risk.

### Impact

The system can be developed faster while still maintaining clear boundaries between domains such as Tenancy, Wallet, Investment, Commission, ExchangeRate, Payment, and Project.

---

## 2. Database Decision: Central Database + Separate Tenant Databases

### Decision

The system uses one central database and separate databases for each tenant.

Example:

```txt
crowdfund_central
tenant_indonesia
tenant_malaysia
````

### Reason

The assessment requires each tenant to have its own database. Tenant data such as issuers, projects, and investments should be isolated per tenant.

Cross-tenant data such as users, investors, agents, wallets, exchange rates, and commission settings are stored in the central database.

### Impact

Tenant data is isolated, while investors and agents can still operate across tenants using a single account.

---

## 3. Tenant Identification Decision: Subdomain-Based Tenancy

### Decision

Tenants are identified using subdomains.

Example:

```txt
crowdfund.test
indonesia.crowdfund.test
malaysia.crowdfund.test
```

### Reason

The assessment requires tenants to run on subdomains. The subdomain is used to identify the tenant, load tenant configuration from the central database, and switch the database connection to the correct tenant database.

### Impact

Each tenant can have its own platform URL while still being served by the same Laravel application.

---

## 4. User Scope Decision

### Decision

Investors and agents are stored in the central database. Issuers and tenant admins are scoped per tenant.

### Reason

Investors and agents must be able to operate across multiple tenants after registering once. Issuers and tenant admins are tenant-specific because they manage projects and operations for a specific tenant.

### Impact

Investor and agent accounts do not need to be duplicated per tenant.

---

## 5. Wallet Decision: Centralized Investor Wallet

### Decision

Investor wallets are stored in the central database.

### Reason

Investors are cross-tenant and can invest in projects from different tenants. Therefore, the wallet must be global and tied to the investor's home currency.

### Rules

* Each investor has one wallet.
* Wallet currency equals investor home currency.
* Wallet mutations must always create ledger entries.
* Wallet balance must not be changed directly without ledger records.

### Impact

The wallet can support cross-tenant investment while keeping financial records auditable.

---

## 6. Money Calculation Decision

### Decision

The system avoids PHP float for monetary calculation.

### Reason

Floating point arithmetic can produce precision errors in financial systems. The system should use decimal values, decimal strings, BCMath, or a money library with currency-aware rounding.

### Rules

* Store currency on every monetary value.
* Use decimal columns for money.
* Use currency-aware rounding.
* IDR and JPY have 0 decimals.
* MYR, SGD, and USD have 2 decimals.
* KWD has 3 decimals.

### Impact

Financial calculations become safer and more predictable.

---

## 7. Exchange Rate Decision: Scraping Instead of API

### Decision

Exchange rates are obtained through scraping, not a ready-made exchange-rate API.

### Reason

The assessment requires exchange rates to be collected through scraping. A Python or Node.js script can be executed by a Laravel queued job or scheduler.

### Impact

The exchange rate module must include scraper execution, error handling, rate persistence, and rate snapshotting.

---

## 8. Exchange Rate Snapshot Decision

### Decision

Every investment and unlock process stores the exact exchange rate used at that time.

### Reason

Old transactions must not change when current exchange rates change.

### Example

When an investor invests MYR into an IDR project, the MYR to IDR rate at investment time is stored. When the investment unlocks, the IDR to MYR rate at unlock time is stored separately.

### Impact

Historical financial values remain consistent and auditable.

---

## 9. Queue Decision

### Decision

Background processes use Laravel Queue.

### Reason

Some processes should not run directly inside HTTP requests, including tenant database provisioning, exchange rate scraping, commission calculation, and investment unlocking.

### Current Development Setup

The development environment uses database queue for simplicity.

### Production Consideration

Redis queue is recommended for production because it is faster and more suitable for background job processing.

---

## 10. Scheduler Decision

### Decision

Laravel Scheduler is used for recurring tasks.

### Reason

Some processes must run automatically, such as scraping exchange rates and unlocking mature investments.

### Scheduled Jobs

* Scrape exchange rates.
* Unlock mature investments.
* Recalculate agent levels.
* Process pending background operations.

---

## 11. Payment Decision: Manual Approval

### Decision

The system uses manual payment approval instead of payment gateway integration.

### Reason

The assessment does not require a payment gateway. Investor top-up, tenant registration fee, and withdrawal are handled manually by admin approval.

### Impact

The system must store payment proof, payment status, approval actor, approval timestamp, and rejection reason.

---

## 12. Project Publishing Decision

### Decision

Projects are only visible after admin review and publishing.

### Reason

Issuer-submitted projects require admin validation before being offered to investors.

### Publishing Rules

* Project must be submitted.
* Admin must fill or verify return values.
* Investor return rate must be less than or equal to 50% of gross return rate.
* Total commission must fit within platform margin.
* Project status must become published before appearing publicly.

---

## 13. Commission Decision

### Decision

Commission calculation is handled by a dedicated commission engine.

### Reason

The system has multiple commission mechanisms, including investor referral commission, agent direct commission, and agent override commission.

### Rules

* Bronze direct commission: 4%.
* Bronze override commission: 0.5%.
* Silver direct commission: 5%.
* Silver override commission: 0.5%.
* Gold direct commission: 6%.
* Gold override commission: 0.7%.
* Commission settings must be editable by admin.
* Total commission must not exceed platform margin.

---

## 14. Investment Lock and Unlock Decision

### Decision

Investments are locked based on the project lock period and unlocked automatically using scheduled jobs.

### Reason

The assessment requires investment funds to remain locked until the configured lock period ends.

### Unlock Rules

* Only locked investments can be unlocked.
* Unlock job must be idempotent.
* Principal and return are calculated in tenant currency.
* Unlock conversion uses exchange rate at unlock time.
* Investor wallet is credited in investor home currency.
* Wallet ledger entry must be created.

---

## 15. Cross-Database Consistency Decision

### Decision

Investment flow writes to both central database and tenant database.

### Reason

Wallet data is central, while investment records are tenant-scoped.

### MVP Approach

For MVP, each database operation uses local database transactions and explicit status transitions.

### Production Consideration

For production, an outbox pattern or saga pattern should be used to improve reliability across multiple databases.

---

## 16. Error Handling Decision

### Decision

The system should use explicit domain exceptions and clear API error responses.

### Reason

Financial and multi-tenant systems must avoid silent failures.

### Examples

* Tenant not found.
* Tenant inactive.
* Exchange rate unavailable.
* Insufficient wallet balance.
* Commission exceeds platform margin.
* Project not publishable.
* Issuer country not allowed.

---

## 17. Documentation Decision

### Decision

The project includes dedicated documentation files.

### Files

```txt
README.md
DECISIONS.md
docs/MVP_SUMMARY.md
docs/ERD.md
docs/COMMISSION_SIMULATION.md
docs/INFRASTRUCTURE.md
docs/WORKFLOW.md
```

### Reason

The assessment requires technical explanation, architecture reasoning, schema diagram, commission simulation, and MVP summary.

---

## 18. Future Improvements

For production, the following improvements are recommended:

* Use Redis queue instead of database queue.
* Add real observability and monitoring.
* Add automated CI/CD.
* Add stronger KYC verification.
* Add stronger audit logging.
* Add outbox/saga pattern for cross-database consistency.
* Add rate source fallback for scraping failure.
* Add admin dashboard UI.
* Add Postman collection and API documentation.
  '@ | Set-Content -Path DECISIONS.md

````


