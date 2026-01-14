<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MerchantRegisterStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $subdomain = $this->input('subdomain');

        if (is_string($subdomain)) {
            $this->merge([
                'subdomain' => Str::lower(trim($subdomain)),
            ]);
        }
    }

    public function rules(): array
    {
        $reserved = Config::get('saas.reserved_subdomains', []);

        return [
            'store_name' => ['required', 'string', 'max:120'],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^(?!-)[a-z0-9-]+(?<!-)$/',
                Rule::notIn($reserved),
                Rule::unique('tenants', 'subdomain'),
            ],
            'terms_accepted' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.regex' => 'Subdomain sadece küçük harf, rakam ve tire içerebilir.',
            'subdomain.not_in' => 'Bu subdomain rezerve edilmiştir. Lütfen başka bir değer seçin.',
            'terms_accepted.accepted' => 'Devam etmek için şartları kabul etmelisiniz.',
        ];
    }
}
