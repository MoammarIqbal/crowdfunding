<?php

namespace App\Domains\Tenancy\Actions;

use App\Domains\Tenancy\Models\Tenant;
use App\Domains\Tenancy\Models\TenantDomain;
use App\Domains\Payment\Models\PaymentRequest;
use App\Domains\Tenancy\Services\TenantDatabaseManager;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class RegisterTenantAction
{
    public function __construct(protected TenantDatabaseManager $databaseManager)
    {
    }

    public function execute(array $data): Tenant
    {
        return DB::connection('central')->transaction(function () use ($data) {
            $registrationFeeAmount = config('tenancy.registration_fee.amount', '100.00000000');
            $registrationFeeCurrency = config('tenancy.registration_fee.currency', 'USD');
            $mainDomain = config('tenancy.main_domain');

            // 1. Create the tenant
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'subdomain' => $data['subdomain'],
                'country_code' => $data['country_code'],
                'currency' => $data['currency'],
                'database_name' => $this->databaseManager->databaseNameForSubdomain($data['subdomain']),
                'status' => TenantStatus::PENDING_PAYMENT->value,
                'registration_fee_amount' => $registrationFeeAmount,
                'registration_fee_currency' => $registrationFeeCurrency,
            ]);

            // 2. Create the primary tenant domain
            TenantDomain::create([
                'tenant_id' => $tenant->id,
                'domain' => $data['subdomain'] . '.' . $mainDomain,
                'is_primary' => true,
            ]);

            // 3. Create the payment request
            PaymentRequest::create([
                'tenant_id' => $tenant->id,
                'type' => 'tenant_registration',
                'amount' => $registrationFeeAmount,
                'currency' => $registrationFeeCurrency,
                'status' => PaymentStatus::PENDING->value,
            ]);

            return $tenant;
        });
    }
}
