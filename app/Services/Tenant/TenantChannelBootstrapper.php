<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Domain as TenantDomain;
use App\Models\Tenant\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantChannelBootstrapper
{
    public function bootstrapForTenant(int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return;
        }

        $domains = TenantDomain::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('type', 'subdomain')
                    ->orWhere(function ($q) {
                        $q->where('type', 'custom')
                            ->whereNotNull('verified_at');
                    });
            })
            ->pluck('domain')
            ->all();

        if (empty($domains)) {
            return;
        }

        foreach ($domains as $host) {
            $normalizedHost = $this->normalizeHost($host);

            if ($normalizedHost === '') {
                continue;
            }

            $this->bootstrapHost($tenantId, $normalizedHost);
        }
    }

    protected function bootstrapHost(int $tenantId, string $host): void
    {
        if (! Schema::hasTable('channels')) {
            return;
        }

        if (DB::table('channels')->where('hostname', $host)->exists()) {
            return;
        }

        $template = DB::table('channels')->where('id', 1)->first();

        if (! $template) {
            $template = DB::table('channels')->orderBy('id')->first();
        }

        if (! $template) {
            Log::warning('TenantChannelBootstrapper: no template channel found', ['tenant_id' => $tenantId]);
            return;
        }

        $code = 'tenant_' . $tenantId . '_' . substr(md5($host), 0, 8);

        DB::transaction(function () use ($template, $code, $host) {
            $payload = $this->buildChannelPayload((array) $template, $code, $host);

            $newChannelId = DB::table('channels')->insertGetId($payload);

            $this->clonePivot('channel_locales', 'locale_id', $template->id, $newChannelId);
            $this->clonePivot('channel_currencies', 'currency_id', $template->id, $newChannelId);

            if (Schema::hasTable('channel_inventory_sources')) {
                $this->clonePivot('channel_inventory_sources', 'inventory_source_id', $template->id, $newChannelId);
            }

            if (Schema::hasTable('channel_translations')) {
                $rows = DB::table('channel_translations')->where('channel_id', $template->id)->get();

                foreach ($rows as $row) {
                    $data = (array) $row;
                    unset($data['id']);

                    $data['channel_id'] = $newChannelId;
                    $data['created_at'] = $data['created_at'] ?? now();
                    $data['updated_at'] = $data['updated_at'] ?? now();

                    DB::table('channel_translations')->insert($data);
                }
            }
        });
    }

    protected function buildChannelPayload(array $template, string $code, string $host): array
    {
        $columns = Schema::getColumnListing('channels');

        $payload = [];

        foreach ($columns as $column) {
            if (in_array($column, ['id', 'code', 'hostname', 'created_at', 'updated_at'], true)) {
                continue;
            }

            if (array_key_exists($column, $template)) {
                $payload[$column] = $template[$column];
            }
        }

        $payload['code'] = $code;
        $payload['hostname'] = $host;

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        return $payload;
    }

    protected function clonePivot(string $table, string $valueColumn, int $fromChannelId, int $toChannelId): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $values = DB::table($table)
            ->where('channel_id', $fromChannelId)
            ->pluck($valueColumn)
            ->all();

        foreach ($values as $value) {
            DB::table($table)->insert([
                'channel_id' => $toChannelId,
                $valueColumn => $value,
            ]);
        }
    }

    protected function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));

        $host = preg_replace('#^https?://#', '', $host) ?? $host;

        if (str_contains($host, '/')) {
            $host = explode('/', $host, 2)[0];
        }

        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return rtrim($host, '.');
    }
}
