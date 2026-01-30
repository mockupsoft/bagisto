<?php

namespace App\Services\Tenant;

use App\Jobs\ProvisionTenantJob;
use App\Models\MerchantUser;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class TenantCreateService
{
    /**
     * Create a new tenant with all required resources.
     */
    public function create(array $data): Tenant
    {
        // Use transaction only for main database operations
        // DB user creation uses different connection and should be outside transaction
        $tenant = null;
        $tenantDb = null;
        
        try {
            $tenant = DB::transaction(function () use ($data, &$tenantDb) {
                // Create tenant record
                $tenant = Tenant::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'store_name' => $data['store_name'] ?? $data['name'],
                    'status' => 'provisioning',
                    'provisioning_started_at' => now(),
                    'last_error' => null,
                ]);

                // Create primary domain
                $primaryDomain = $this->createPrimaryDomain($tenant, $data);

                // Create tenant database record
                $tenantDb = $this->createTenantDatabase($tenant, $data);

                // Create merchant user (admin credentials) - auto-generate if not provided
                $merchantUser = $this->createMerchantUser($tenant, $data);

                return $tenant;
            });

            // Refresh tenant to get latest data
            $tenant->refresh();
            $tenantDb = $tenant->database;

            // Create database user OUTSIDE transaction (uses different connection)
            // This prevents "no active transaction" errors
            if ($tenantDb) {
                $dbConfig = Config::get('saas.tenant_db', []);
                $defaultUsername = $dbConfig['username'] ?? 'root';
                if ($tenantDb->database_username !== $defaultUsername && !empty($tenantDb->database_password)) {
                    $this->createDatabaseUser($tenantDb, $tenantDb->database_username, $tenantDb->database_password);
                }
            }

            // Dispatch provisioning job if requested
            if ($data['provision_now'] ?? true) {
                $merchantUser = MerchantUser::where('tenant_id', $tenant->id)->first();
                if ($merchantUser) {
                    dispatch(new ProvisionTenantJob(
                        tenantId: $tenant->id,
                        adminEmail: $merchantUser->email,
                        adminPasswordHash: $merchantUser->password,
                        adminName: $merchantUser->name
                    ));
                }
            }

            return $tenant;
        } catch (Throwable $e) {
            // If tenant was created but something failed, update error
            if ($tenant) {
                $tenant->update([
                    'status' => 'failed',
                    'last_error' => mb_strimwidth($e->getMessage(), 0, 500),
                ]);
            }
            throw $e;
        }
    }

    /**
     * Create primary domain for tenant.
     */
    protected function createPrimaryDomain(Tenant $tenant, array $data): Domain
    {
        $baseDomain = Config::get('saas.base_domain', 'example.test');
        
        // Use custom domain if provided and not empty, otherwise generate from slug
        $customDomain = trim($data['primary_domain'] ?? '');
        if (empty($customDomain)) {
            // Generate from slug
            $domainValue = $tenant->slug . '.' . $baseDomain;
            $isCustom = false;
        } else {
            $domainValue = $customDomain;
            $isCustom = true;
        }
        
        // Normalize domain
        $domainValue = strtolower(trim($domainValue));
        
        // Ensure domain is not empty
        if (empty($domainValue)) {
            throw new \InvalidArgumentException('Domain cannot be empty. Please provide a valid slug or primary domain.');
        }
        
        return Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => $domainValue,
            'type' => $isCustom ? 'custom' : 'subdomain',
            'is_primary' => true,
            'verified_at' => $isCustom ? null : now(), // Subdomain auto-verified
            'created_by_id' => auth()->id(),
            'note' => null,
        ]);
    }

    /**
     * Create tenant database record.
     */
    protected function createTenantDatabase(Tenant $tenant, array $data): TenantDatabase
    {
        $dbConfig = Config::get('saas.tenant_db', []);
        
        // Generate database name if not provided
        $dbName = $data['db_name'] ?? (($dbConfig['name_prefix'] ?? 'tenant_') . $tenant->id);
        
        // Generate database username if not provided
        // If auto_generate_db is true or db_username is empty, create per-tenant user
        $defaultUsername = $dbConfig['username'] ?? 'root';
        $autoGenerateDb = $data['auto_generate_db'] ?? true;
        
        if ($autoGenerateDb && empty($data['db_username'])) {
            // Generate per-tenant username: tenant_{id}_user
            $dbUsername = 'tenant_' . $tenant->id . '_user';
            // Always generate password for per-tenant users
            $dbPassword = $data['db_password'] ?? Str::random(16);
        } else {
            $dbUsername = $data['db_username'] ?? $defaultUsername;
            // If using default username, use config password (can be empty)
            // If using custom username, must have password
            if (!empty($data['db_username']) && $data['db_username'] !== $defaultUsername) {
                $dbPassword = $data['db_password'] ?? Str::random(16);
            } else {
                $dbPassword = $data['db_password'] ?? ($dbConfig['password'] ?? '');
            }
        }

        return TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => $dbName,
            'database_host' => $data['db_host'] ?? ($dbConfig['host'] ?? '127.0.0.1'),
            'database_port' => $dbConfig['port'] ?? 3306,
            'database_username' => $dbUsername,
            'database_password' => $dbPassword,
            'database_prefix' => $dbConfig['prefix'] ?? '',
            'status' => 'pending',
            'last_error' => null,
        ]);
    }

    /**
     * Create database user for tenant.
     */
    protected function createDatabaseUser(TenantDatabase $tenantDb, string $username, string $password): void
    {
        try {
            $templateConnection = Config::get('saas.tenant_db.connection_template', 'mysql');
            $dbName = $tenantDb->database_name;
            
            // Escape identifiers
            $escapedUsername = str_replace(['`', "'"], ['``', "''"], $username);
            $escapedPassword = str_replace("'", "''", $password);
            $escapedDbName = str_replace('`', '``', $dbName);
            
            // Create user if not exists
            $createUserSql = sprintf(
                "CREATE USER IF NOT EXISTS '%s'@'%%' IDENTIFIED BY '%s'",
                $escapedUsername,
                $escapedPassword
            );
            
            DB::connection($templateConnection)->statement($createUserSql);
            
            // Grant privileges
            $grantSql = sprintf(
                "GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%%'",
                $escapedDbName,
                $escapedUsername
            );
            
            DB::connection($templateConnection)->statement($grantSql);
            
            // Flush privileges
            DB::connection($templateConnection)->statement('FLUSH PRIVILEGES');
            
        } catch (Throwable $e) {
            // Log error but don't fail tenant creation
            \Log::error('Failed to create database user for tenant', [
                'tenant_id' => $tenantDb->tenant_id,
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
            
            // Update tenant database record with error
            $tenantDb->update([
                'last_error' => 'Database user creation failed: ' . mb_strimwidth($e->getMessage(), 0, 500),
            ]);
            
            // Don't throw - allow tenant creation to continue
            // The provisioning job will handle DB user creation retry if needed
        }
    }

    /**
     * Create merchant user (admin credentials).
     */
    protected function createMerchantUser(Tenant $tenant, array $data): MerchantUser
    {
        // Auto-generate admin credentials if not provided or empty
        $adminEmailInput = trim($data['admin_email'] ?? '');
        $adminNameInput = trim($data['admin_name'] ?? '');
        $adminPasswordInput = trim($data['admin_password'] ?? '');
        
        if (empty($adminEmailInput)) {
            $adminEmail = 'admin@' . $tenant->slug . '.' . Config::get('saas.base_domain', 'example.test');
        } else {
            $adminEmail = $adminEmailInput;
        }
        
        if (empty($adminNameInput)) {
            $adminName = $tenant->name . ' Admin';
        } else {
            $adminName = $adminNameInput;
        }
        
        if (empty($adminPasswordInput)) {
            $adminPassword = Str::random(16);
        } else {
            $adminPassword = $adminPasswordInput;
        }

        return MerchantUser::updateOrCreate(
            [
                'email' => $adminEmail,
                'tenant_id' => $tenant->id,
            ],
            [
                'name' => $adminName,
                'password' => bcrypt($adminPassword),
            ]
        );
    }
}
