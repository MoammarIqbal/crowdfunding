<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainApp\TenantRegistrationController;

// Main App API routes
Route::post('/tenant-registrations', [TenantRegistrationController::class, 'store']);

// Tenant routes are registered in bootstrap/app.php
