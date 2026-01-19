<?php

namespace App\Services;

use App\Jobs\ProvisionTenantJob;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TenantProvisioningService
{
    protected const SESSION_KEY = 'onboarding.merchant_register';

    public function startFromOnboardingSession(): Tenant
    {
        $session = session()->get(self::SESSION_KEY, []);
        $existingId = Arr::get($session, 'tenant_id');

        if ($existingId) {
            $existing = Tenant::find($existingId);

            if ($existing) {
                return $existing;
            }
        }

        $step1 = Arr::get($session, 'step1', []);
        $step2 = Arr::get($session, 'step2', []);
        $step3 = Arr::get($session, 'step3', []);

        if (empty($step1['email']) || empty($step1['password_encrypted'])) {
            throw new RuntimeException('onboarding_incomplete');
        }

        if (empty($step3['store_name']) || empty($step3['subdomain'])) {
            throw new RuntimeException('onboarding_incomplete');
        }

        $storeName = $step3['store_name'];
        $subdomain = Str::lower($step3['subdomain']);

        $adminName = trim(($step2['first_name'] ?? '') . ' ' . ($step2['last_name'] ?? ''));
        $adminName = $adminName !== '' ? $adminName : $storeName;

        $adminEmail = $step1['email'];
        $adminPassword = decrypt($step1['password_encrypted']);
        $adminPasswordHash = bcrypt($adminPassword);

        $tenant = DB::transaction(function () use ($storeName, $subdomain) {
            $slugBase = Str::slug($subdomain ?: $storeName) ?: Str::slug($storeName);
            $slug = $this->uniqueSlug($slugBase);

            $tenant = Tenant::create([
                'name' => $storeName,
                'store_name' => $storeName,
                'slug' => $slug,
                'subdomain' => $subdomain,
                'status' => 'provisioning',
                'provisioning_started_at' => now(),
                'onboarding_completed_at' => now(),
                'last_error' => null,
            ]);

            $baseDomain = Config::get('saas.base_domain', 'example.test');
            $primaryDomain = $subdomain . '.' . $baseDomain;

            Domain::create([
                'tenant_id' => $tenant->id,
                'domain' => $primaryDomain,
                'type' => 'subdomain',
                'is_primary' => true,
                'verified_at' => null,
                'created_by_id' => null,
                'note' => null,
            ]);

            $dbConfig = Config::get('saas.tenant_db', []);
            $dbName = ($dbConfig['name_prefix'] ?? 'tenant_') . $tenant->id;

            TenantDatabase::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'database_name' => $dbName,
                    'database_host' => $dbConfig['host'] ?? '127.0.0.1',
                    'database_port' => $dbConfig['port'] ?? 3306,
                    'database_username' => $dbConfig['username'] ?? 'root',
                    'database_password' => $dbConfig['password'] ?? '',
                    'database_prefix' => $dbConfig['prefix'] ?? '',
                    'status' => 'pending',
                    'last_error' => null,
                ]
            );

            return $tenant;
        });

        $merchantUser = \App\Models\MerchantUser::updateOrCreate(
            ['email' => $adminEmail],
            [
                'tenant_id' => $tenant->id,
                'name' => $adminName,
                'password' => $adminPasswordHash,
            ]
        );

        dispatch(new ProvisionTenantJob(
            tenantId: $tenant->id,
            adminEmail: $merchantUser->email,
            adminPasswordHash: $merchantUser->password,
            adminName: $merchantUser->name
        ));

        session()->put(self::SESSION_KEY . '.tenant_id', $tenant->id);

        return $tenant;
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base ?: Str::random(8);
        $counter = 1;

        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
