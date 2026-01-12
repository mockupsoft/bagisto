<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Domain;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DomainVerificationService
{
    public function start(Domain $domain, ?string $method = 'dns_txt'): Domain
    {
        $domain->forceFill([
            'verification_token' => Str::random(40),
            'verification_method' => $method ?? 'dns_txt',
            'verification_started_at' => Carbon::now(),
            'verified_at' => null,
        ])->save();

        return $domain->refresh();
    }

    public function markVerified(Domain $domain): Domain
    {
        $domain->forceFill([
            'verified_at' => Carbon::now(),
        ])->save();

        return $domain->refresh();
    }
}
