<?php

namespace App\Console\Commands;

use App\Models\Tenant\Domain;
use App\Services\Tenant\DomainVerificationService;
use Illuminate\Console\Command;

class VerifyDomainCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:domains:verify {domainId} {--method=} {--force-start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify a custom domain using DNS TXT or HTTP file method';

    /**
     * Execute the console command.
     */
    public function handle(DomainVerificationService $service): int
    {
        $domainId = $this->argument('domainId');
        $domain = Domain::find($domainId);

        if (! $domain) {
            $this->error("Domain with ID {$domainId} not found.");
            return Command::FAILURE;
        }

        $this->info("Verifying domain: {$domain->domain} (ID: {$domain->id})");

        // Update method if provided
        if ($this->option('method')) {
            $method = $this->option('method');
            if (! in_array($method, ['dns_txt', 'http_file'])) {
                $this->error("Invalid method. Use 'dns_txt' or 'http_file'.");
                return Command::FAILURE;
            }
            $domain->forceFill(['verification_method' => $method])->save();
            $this->info("Verification method set to: {$method}");
        }

        // Force start if requested
        if ($this->option('force-start')) {
            $domain = $service->start($domain, $domain->verification_method ?? 'dns_txt');
            $this->info("Verification token regenerated.");
        }

        // Attempt verification
        $result = $service->attemptVerify($domain);

        if ($result['ok']) {
            $this->info("✓ VERIFIED");
            if ($result['reason']) {
                $this->comment("Reason: {$result['reason']}");
            }
            return Command::SUCCESS;
        }

        $this->error("✗ VERIFICATION FAILED");
        $this->comment("Method: {$result['method']}");
        $this->comment("Reason: {$result['reason']}");
        
        return Command::FAILURE;
    }
}
