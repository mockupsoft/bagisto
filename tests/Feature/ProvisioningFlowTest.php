<?php

namespace Tests\Feature;

use App\Jobs\ProvisionTenantJob;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Services\Tenant\TenantCatalogSeeder;
use App\Services\Tenant\TenantConnectionConfigurator;
use App\Services\Tenant\TenantCustomerSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProvisioningFlowTest extends TestCase
{
    public function test_onboarding_complete_dispatches_job_and_reports_status(): void
    {
        Queue::fake();

        config()->set('saas.base_domain', 'localhost');

        $payload = [
            'step1' => [
                'email' => 'merchant@example.com',
                'password_encrypted' => encrypt('password123'),
            ],
            'step2' => [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'phone' => null,
            ],
        ];

        $response = $this->withSession([
            'onboarding.merchant_register' => $payload,
        ])->post(route('merchant.register.complete'), [
            'store_name' => 'Acme Store',
            'subdomain' => 'acme-store',
            'terms_accepted' => '1',
        ]);

        $tenantId = session('onboarding.merchant_register.tenant_id');
        $this->assertNotNull($tenantId);

        $tenant = Tenant::find($tenantId);
        $this->assertNotNull($tenant);
        $this->assertSame('provisioning', $tenant->status);
        $this->assertSame('Acme Store', $tenant->store_name);
        $this->assertSame('acme-store', $tenant->subdomain);

        $job = null;

        Queue::assertPushed(ProvisionTenantJob::class, function ($pushedJob) use ($tenantId, &$job) {
            $job = $pushedJob;

            return $pushedJob->tenantId === $tenantId
                && $pushedJob->adminEmail === 'merchant@example.com'
                && $pushedJob->adminName === 'Jane Doe'
                && ! empty($pushedJob->adminPasswordHash);
        });

        $response->assertRedirect(route('provisioning.progress', ['tenant' => $tenantId]));

        $statusResponse = $this->withSession([
            'onboarding.merchant_register' => session('onboarding.merchant_register'),
        ])->get(route('provisioning.status', ['tenant' => $tenantId]));

        $statusResponse->assertOk();
        $statusResponse->assertJsonFragment(['status' => 'provisioning']);

        $templateConnection = config('saas.tenant_db.connection_template', config('database.default'));

        try {
            if (env('RUN_TENANT_DDL_TESTS', false)) {
                $this->assertNotNull($job);

                $job->handle(
                    app(TenantConnectionConfigurator::class),
                    app(TenantCatalogSeeder::class),
                    app(TenantCustomerSeeder::class)
                );

                $tenant->refresh();
                $this->assertSame('active', $tenant->status);

                $tenantDb = $tenant->database()->first();
                $this->assertNotNull($tenantDb);
                $this->assertSame('ready', $tenantDb->status);

                app(TenantConnectionConfigurator::class)->configure($tenantDb);
                DB::purge('tenant');
                DB::reconnect('tenant');

                $this->assertSame(
                    1,
                    DB::connection('tenant')->table('admins')->where('email', 'merchant@example.com')->count()
                );

                $statusResponseAfter = $this->withSession([
                    'onboarding.merchant_register' => session('onboarding.merchant_register'),
                ])->get(route('provisioning.status', ['tenant' => $tenantId]));

                $statusResponseAfter->assertOk();
                $statusResponseAfter->assertJsonFragment(['status' => 'active']);
                $statusResponseAfter->assertJsonFragment(['db_status' => 'ready']);
            }
        } finally {
            $tenantDb = $tenant->database()->first();

            if ($tenantDb && $tenantDb->database_name) {
                DB::connection($templateConnection)->statement(
                    'DROP DATABASE IF EXISTS `' . str_replace('`', '``', $tenantDb->database_name) . '`'
                );
            }

            Domain::where('tenant_id', $tenantId)->delete();

            $tenant->database()->delete();
            $tenant->delete();
        }
    }
}
