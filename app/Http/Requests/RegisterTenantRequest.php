<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', 'unique:tenants,slug'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9\-]+$/',
                'unique:tenants,subdomain',
                function ($attribute, $value, $fail) {
                    $reserved = [
                        'www', 'admin', 'api', 'app', 'dashboard', 'root',
                        'support', 'help', 'mail', 'ftp', 'staging', 'dev', 'test'
                    ];
                    if (in_array(strtolower($value), $reserved)) {
                        $fail('The selected subdomain is reserved and cannot be used.');
                    }
                },
            ],
            'country_code' => ['required', 'string', 'size:2'],
            'currency' => ['required', 'string', 'size:3'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => strtolower($this->slug)]);
        }
        if ($this->has('subdomain')) {
            $this->merge(['subdomain' => strtolower($this->subdomain)]);
        }
        if ($this->has('country_code')) {
            $this->merge(['country_code' => strtoupper($this->country_code)]);
        }
        if ($this->has('currency')) {
            $this->merge(['currency' => strtoupper($this->currency)]);
        }
    }
}
