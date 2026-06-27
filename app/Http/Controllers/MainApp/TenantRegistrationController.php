<?php

namespace App\Http\Controllers\MainApp;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterTenantRequest;
use App\Domains\Tenancy\Actions\RegisterTenantAction;
use Illuminate\Http\JsonResponse;

class TenantRegistrationController extends Controller
{
    public function __construct(protected RegisterTenantAction $registerTenantAction)
    {
    }

    public function store(RegisterTenantRequest $request): JsonResponse
    {
        $tenant = $this->registerTenantAction->execute($request->validated());
        
        $paymentRequest = $tenant->paymentRequests()->where('type', 'tenant_registration')->first();

        return response()->json([
            'message' => 'Tenant registered successfully. Please complete the payment to proceed.',
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'tenant_slug' => $tenant->slug,
            'tenant_subdomain' => $tenant->subdomain,
            'tenant_status' => $tenant->status->value,
            'tenant_domain' => $tenant->domains()->where('is_primary', true)->value('domain'),
            'registration_fee_amount' => $tenant->registration_fee_amount,
            'registration_fee_currency' => $tenant->registration_fee_currency,
            'payment_request_id' => $paymentRequest?->id,
            'payment_request_status' => $paymentRequest?->status,
        ], 201);
    }
}
