<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // ACL kontrolü middleware'de yapılacak
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('tenants', 'slug')->whereNull('deleted_at'),
            ],
            'store_name' => ['nullable', 'string', 'max:255'],
            'primary_domain' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('domains', 'domain'),
            ],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_name' => ['nullable', 'string', 'max:255'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'min:8'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_password' => ['nullable', 'string', 'min:8'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'auto_generate_admin' => ['nullable', 'boolean'],
            'auto_generate_db' => ['nullable', 'boolean'],
            'provision_now' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => trans('admin::app.tenants.create.validation.name.required'),
            'slug.required' => trans('admin::app.tenants.create.validation.slug.required'),
            'slug.regex' => trans('admin::app.tenants.create.validation.slug.regex'),
            'slug.unique' => trans('admin::app.tenants.create.validation.slug.unique'),
            'primary_domain.unique' => trans('admin::app.tenants.create.validation.primary_domain.unique'),
            'admin_email.email' => trans('admin::app.tenants.create.validation.admin_email.email'),
            'admin_password.min' => trans('admin::app.tenants.create.validation.admin_password.min'),
            'db_password.min' => trans('admin::app.tenants.create.validation.db_password.min'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Slug'ı otomatik oluştur eğer boşsa veya sadece whitespace ise
        if ($this->has('name') && (empty($this->input('slug')) || trim($this->input('slug')) === '')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->input('name')),
            ]);
        } elseif ($this->has('slug')) {
            // Slug'ı normalize et (lowercase, trim)
            $slug = strtolower(trim($this->input('slug')));
            $this->merge([
                'slug' => $slug,
            ]);
        }

        // DB host default değeri
        if (! $this->has('db_host') || empty($this->input('db_host'))) {
            $this->merge([
                'db_host' => config('saas.tenant_db.host', '127.0.0.1'),
            ]);
        }

        // Provision now default true
        if (! $this->has('provision_now')) {
            $this->merge([
                'provision_now' => true,
            ]);
        }
    }
}
