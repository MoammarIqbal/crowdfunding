<?php

namespace App\Domains\Tenancy\Models;

use App\Support\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'central';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'country_code',
        'currency',
        'database_name',
        'status',
        'registration_fee_amount',
        'registration_fee_currency',
        'approved_at',
        'activated_at',
        'suspended_at',
        'rejected_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'registration_fee_amount' => 'decimal:8',
            'approved_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Get the domains for the tenant.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    /**
     * Get the payment requests for the tenant.
     */
    public function paymentRequests(): HasMany
    {
        return $this->hasMany(\App\Domains\Payment\Models\PaymentRequest::class, 'tenant_id', 'id');
    }

    /**
     * Check if the tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    /**
     * Check if the tenant is provisioning.
     */
    public function isProvisioning(): bool
    {
        return $this->status === TenantStatus::PROVISIONING;
    }

    /**
     * Check if the tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    /**
     * Check if the tenant is pending in any form.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [
            TenantStatus::PENDING_REGISTRATION,
            TenantStatus::PENDING_PAYMENT,
            TenantStatus::PENDING_APPROVAL,
        ]);
    }
}
