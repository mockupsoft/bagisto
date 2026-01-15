<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantCustomerSeeder
{
    /**
     * Seed both global prerequisites and tenant-scoped customer tables.
     */
    public function seed(): void
    {
        $this->seedGlobal();
        $this->seedTenant();
    }

    /**
     * Seed global Bagisto prerequisites used by customer flows.
     *
     * These tables live in the global DB (not tenant DB): channels, locales, currencies, categories, countries.
     */
    public function seedGlobal(): void
    {
        $connName = config('saas.tenant_db.connection_template', config('database.default'));
        $conn = DB::connection($connName);
        $schema = Schema::connection($connName);
        $now = now();

        if (! $schema->hasTable('locales')) {
            return;
        }

        $localeId = $conn->table('locales')->where('code', 'en')->value('id');

        if (! $localeId) {
            $insert = [
                'code' => 'en',
                'name' => 'English',
            ];

            if ($conn->getSchemaBuilder()->hasColumn('locales', 'direction')) {
                $insert['direction'] = 'ltr';
            }

            if ($conn->getSchemaBuilder()->hasColumn('locales', 'created_at')) {
                $insert['created_at'] = $now;
            }

            if ($conn->getSchemaBuilder()->hasColumn('locales', 'updated_at')) {
                $insert['updated_at'] = $now;
            }

            $localeId = $conn->table('locales')->insertGetId($insert);
        }

        if ($schema->hasTable('currencies')) {
            $currencyId = $conn->table('currencies')->where('code', 'USD')->value('id');

            if (! $currencyId) {
                $insert = [
                    'code' => 'USD',
                    'name' => 'US Dollar',
                ];

                if ($conn->getSchemaBuilder()->hasColumn('currencies', 'symbol')) {
                    $insert['symbol'] = '$';
                }

                if ($conn->getSchemaBuilder()->hasColumn('currencies', 'created_at')) {
                    $insert['created_at'] = $now;
                }

                if ($conn->getSchemaBuilder()->hasColumn('currencies', 'updated_at')) {
                    $insert['updated_at'] = $now;
                }

                $currencyId = $conn->table('currencies')->insertGetId($insert);
            }
        } else {
            $currencyId = null;
        }

        if ($schema->hasTable('countries')) {
            $countryId = $conn->table('countries')->where('code', 'TR')->value('id');

            if (! $countryId) {
                $insert = [
                    'code' => 'TR',
                    'name' => 'Turkey',
                ];

                if ($conn->getSchemaBuilder()->hasColumn('countries', 'created_at')) {
                    $insert['created_at'] = $now;
                }

                if ($conn->getSchemaBuilder()->hasColumn('countries', 'updated_at')) {
                    $insert['updated_at'] = $now;
                }

                $countryId = $conn->table('countries')->insertGetId($insert);
            }

            if ($schema->hasTable('country_states')) {
                $stateExists = $conn->table('country_states')
                    ->where('country_id', $countryId)
                    ->where('code', 'TR-34')
                    ->exists();

                if (! $stateExists) {
                    $insert = [
                        'country_id' => $countryId,
                        'code' => 'TR-34',
                    ];

                    if ($conn->getSchemaBuilder()->hasColumn('country_states', 'default_name')) {
                        $insert['default_name'] = 'Istanbul';
                    }

                    if ($conn->getSchemaBuilder()->hasColumn('country_states', 'created_at')) {
                        $insert['created_at'] = $now;
                    }

                    if ($conn->getSchemaBuilder()->hasColumn('country_states', 'updated_at')) {
                        $insert['updated_at'] = $now;
                    }

                    $conn->table('country_states')->insert($insert);
                }
            }
        }

        if ($schema->hasTable('categories')) {
            $rootId = $conn->table('categories')->where('id', 1)->value('id');

            if (! $rootId) {
                $insert = ['id' => 1];

                if ($conn->getSchemaBuilder()->hasColumn('categories', 'parent_id')) {
                    $insert['parent_id'] = null;
                }

                if ($conn->getSchemaBuilder()->hasColumn('categories', 'position')) {
                    $insert['position'] = 1;
                }

                if ($conn->getSchemaBuilder()->hasColumn('categories', 'status')) {
                    $insert['status'] = 1;
                }

                if ($conn->getSchemaBuilder()->hasColumn('categories', '_lft')) {
                    $insert['_lft'] = 1;
                }

                if ($conn->getSchemaBuilder()->hasColumn('categories', '_rgt')) {
                    $insert['_rgt'] = 2;
                }

                if ($conn->getSchemaBuilder()->hasColumn('categories', 'created_at')) {
                    $insert['created_at'] = $now;
                }

                if ($conn->getSchemaBuilder()->hasColumn('categories', 'updated_at')) {
                    $insert['updated_at'] = $now;
                }

                $conn->table('categories')->insert($insert);
                $rootId = 1;
            }
        } else {
            $rootId = null;
        }

        if ($schema->hasTable('channels')) {
            $channel = $conn->table('channels')->where('code', 'default')->first();

            if (! $channel) {
                $payload = [
                    'code' => 'default',
                    'name' => 'Default',
                ];

                if ($conn->getSchemaBuilder()->hasColumn('channels', 'description')) {
                    $payload['description'] = 'Default Channel';
                }

                if ($conn->getSchemaBuilder()->hasColumn('channels', 'hostname')) {
                    $payload['hostname'] = 'localhost';
                }

                if ($conn->getSchemaBuilder()->hasColumn('channels', 'theme')) {
                    $payload['theme'] = 'default';
                }

                if (! is_null($rootId) && $conn->getSchemaBuilder()->hasColumn('channels', 'root_category_id')) {
                    $payload['root_category_id'] = $rootId;
                }

                if ($conn->getSchemaBuilder()->hasColumn('channels', 'default_locale_id')) {
                    $payload['default_locale_id'] = $localeId;
                }

                if (! is_null($currencyId) && $conn->getSchemaBuilder()->hasColumn('channels', 'base_currency_id')) {
                    $payload['base_currency_id'] = $currencyId;
                }

                if ($conn->getSchemaBuilder()->hasColumn('channels', 'created_at')) {
                    $payload['created_at'] = $now;
                }

                if ($conn->getSchemaBuilder()->hasColumn('channels', 'updated_at')) {
                    $payload['updated_at'] = $now;
                }

                $channelId = $conn->table('channels')->insertGetId($payload);
            } else {
                $channelId = $channel->id;
            }

            if ($schema->hasTable('channel_locales')) {
                $hasPivotLocale = $conn->table('channel_locales')
                    ->where('channel_id', $channelId)
                    ->where('locale_id', $localeId)
                    ->exists();

                if (! $hasPivotLocale) {
                    $row = [
                        'channel_id' => $channelId,
                        'locale_id' => $localeId,
                    ];

                    if ($conn->getSchemaBuilder()->hasColumn('channel_locales', 'created_at')) {
                        $row['created_at'] = $now;
                    }

                    if ($conn->getSchemaBuilder()->hasColumn('channel_locales', 'updated_at')) {
                        $row['updated_at'] = $now;
                    }

                    $conn->table('channel_locales')->insert($row);
                }
            }

            if (! is_null($currencyId) && $schema->hasTable('channel_currencies')) {
                $hasPivotCurrency = $conn->table('channel_currencies')
                    ->where('channel_id', $channelId)
                    ->where('currency_id', $currencyId)
                    ->exists();

                if (! $hasPivotCurrency) {
                    $row = [
                        'channel_id' => $channelId,
                        'currency_id' => $currencyId,
                    ];

                    if ($conn->getSchemaBuilder()->hasColumn('channel_currencies', 'created_at')) {
                        $row['created_at'] = $now;
                    }

                    if ($conn->getSchemaBuilder()->hasColumn('channel_currencies', 'updated_at')) {
                        $row['updated_at'] = $now;
                    }

                    $conn->table('channel_currencies')->insert($row);
                }
            }
        }
    }

    /**
     * Seed tenant-scoped customer prerequisites.
     */
    public function seedTenant(): void
    {
        $conn = DB::connection('tenant');
        $schema = Schema::connection('tenant');
        $now = now();

        if (! $schema->hasTable('customer_groups')) {
            return;
        }

        $exists = $conn->table('customer_groups')->where('code', 'general')->exists();

        if (! $exists) {
            $conn->table('customer_groups')->insert([
                'code' => 'general',
                'name' => 'General',
                'is_user_defined' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
