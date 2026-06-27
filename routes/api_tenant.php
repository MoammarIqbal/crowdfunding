<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Tenancy\Services\TenantContext;

Route::middleware([
    'identify.tenant',
    'ensure.tenant.active',
    'switch.tenant.database',
])->group(function () {
    Route::get('/tenant-context', function (TenantContext $context) {
        $tenant = $context->get();
        return response()->json([
            'id' => $tenant?->id,
            'name' => $tenant?->name,
            'subdomain' => $tenant?->subdomain,
            'database_name' => $tenant?->database_name,
            'status' => $tenant?->status?->value,
            'note' => 'This is a temporary diagnostic route.',
        ]);
    });
});
