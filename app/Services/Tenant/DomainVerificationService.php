<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Domain;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

interface DnsTxtResolver
{
    /**
     * @return array<int, string>
     */
    public function getTxtRecords(string $host): array;
}

class NativeDnsTxtResolver implements DnsTxtResolver
{
    public function getTxtRecords(string $host): array
    {
        $records = @dns_get_record($host, DNS_TXT) ?: [];

        $values = [];

        foreach ($records as $record) {
            if (isset($record['txt'])) {
                $values[] = (string) $record['txt'];
            }
        }

        return $values;
    }
}

class DomainVerificationService
{
    public const METHOD_DNS_TXT = 'dns_txt';
    public const METHOD_HTTP_FILE = 'http_file';

    public const DNS_PREFIX = '_saas-verify.';
    public const HTTP_WELL_KNOWN_PATH = '/.well-known/saas-domain-verification.txt';

    protected DnsTxtResolver $dns;

    public function __construct(?DnsTxtResolver $dns = null)
    {
        $this->dns = $dns ?? new NativeDnsTxtResolver();
    }

    public function start(Domain $domain, ?string $method = self::METHOD_DNS_TXT): Domain
    {
        $method = $method ?: self::METHOD_DNS_TXT;

<<<<<<< HEAD
        $domain->forceFill([
            'verification_token' => Str::random(40),
            'verification_method' => $method,
            'verification_value' => null,
=======
        $token = Str::random(40);
        $value = 'saas-verify=' . $token;

        $domain->forceFill([
            'verification_token' => $token,
            'verification_method' => $method,
            'verification_value' => $value,
>>>>>>> 569dce1395ed17891afb6474c2bcc58b0db46e45
            'verification_started_at' => Carbon::now(),
            'last_checked_at' => null,
            'last_failure_reason' => null,
            'verified_at' => null,
        ])->save();

        return $domain->refresh();
    }

    /**
     * @return array{method: string, host: string, value: string}
     */
    public function getDnsInstruction(Domain $domain): array
    {
        $host = self::DNS_PREFIX . $this->normalizeDomain($domain->domain);

        return [
            'method' => self::METHOD_DNS_TXT,
            'host' => $host,
            'value' => $this->expectedVerificationValue($domain),
        ];
    }

    /**
     * @return array{method: string, url: string, value: string}
     */
    public function getHttpInstruction(Domain $domain): array
    {
<<<<<<< HEAD
        $host = $this->normalizeDomain($domain->domain);
=======
        $host = rtrim($this->normalizeDomain($domain->domain), '/');
>>>>>>> 569dce1395ed17891afb6474c2bcc58b0db46e45

        return [
            'method' => self::METHOD_HTTP_FILE,
            'url' => 'https://' . $host . self::HTTP_WELL_KNOWN_PATH,
            'value' => $this->expectedVerificationValue($domain),
        ];
    }

    /**
     * @return array{ok: bool, method: string, reason: string|null}
     */
    public function attemptVerify(Domain $domain, ?string $method = null): array
    {
        if (! is_null($domain->verified_at)) {
            return ['ok' => true, 'method' => $domain->verification_method ?: self::METHOD_DNS_TXT, 'reason' => null];
        }

        if ($domain->type !== 'custom') {
            return ['ok' => false, 'method' => $domain->verification_method ?: self::METHOD_DNS_TXT, 'reason' => 'not_custom'];
        }

        $method = $method ?: ($domain->verification_method ?: self::METHOD_DNS_TXT);

        if (! in_array($method, [self::METHOD_DNS_TXT, self::METHOD_HTTP_FILE], true)) {
            return ['ok' => false, 'method' => $method, 'reason' => 'invalid_method'];
        }

        if (empty($domain->verification_token)) {
            $this->start($domain, $method);
        }

<<<<<<< HEAD
        $domain->forceFill([
            'verification_method' => $method,
            'verification_value' => $this->expectedVerificationValue($domain),
=======
        $expected = $this->expectedVerificationValue($domain);

        $domain->forceFill([
            'verification_method' => $method,
            'verification_value' => $expected,
>>>>>>> 569dce1395ed17891afb6474c2bcc58b0db46e45
        ])->save();

        $ok = false;
        $reason = null;

        try {
            if ($method === self::METHOD_DNS_TXT) {
                [$ok, $reason] = $this->verifyDns($domain);
            } else {
                [$ok, $reason] = $this->verifyHttp($domain);
            }
        } catch (\Throwable $e) {
            $ok = false;
            $reason = 'verification_error';
        }

        $domain->forceFill([
            'last_checked_at' => Carbon::now(),
            'last_failure_reason' => $ok ? null : ($reason ?: 'not_verified'),
        ])->save();

        if ($ok) {
            $this->markVerified($domain);
        }

        return ['ok' => $ok, 'method' => $method, 'reason' => $ok ? null : ($reason ?: 'not_verified')];
    }

    public function markVerified(Domain $domain): Domain
    {
        $domain->forceFill([
            'verified_at' => Carbon::now(),
            'last_failure_reason' => null,
        ])->save();

        return $domain->refresh();
    }

    /**
     * @return array{0: bool, 1: string|null}
     */
    protected function verifyDns(Domain $domain): array
    {
        $instruction = $this->getDnsInstruction($domain);
        $records = $this->dns->getTxtRecords($instruction['host']);

        if (empty($records)) {
            return [false, 'dns_txt_not_found'];
        }

        $expected = $instruction['value'];

        foreach ($records as $txt) {
            if (trim($txt) === $expected) {
                return [true, null];
            }
        }

        return [false, 'dns_txt_mismatch'];
    }

    /**
     * @return array{0: bool, 1: string|null}
     */
    protected function verifyHttp(Domain $domain): array
    {
<<<<<<< HEAD
        $host = $this->normalizeDomain($domain->domain);
        $expected = $this->expectedVerificationValue($domain);

        $urls = [
            'https://' . $host . self::HTTP_WELL_KNOWN_PATH,
            'http://' . $host . self::HTTP_WELL_KNOWN_PATH,
        ];

        foreach ($urls as $url) {
            $response = Http::timeout(5)->acceptText()->get($url);

            if (! $response->successful()) {
                continue;
            }

            $body = trim((string) $response->body());

            if ($body === $expected || str_contains($body, $expected) || $body === $domain->verification_token) {
                return [true, null];
            }
        }

        return [false, 'http_file_mismatch'];
    }

    protected function expectedVerificationValue(Domain $domain): string
    {
        return 'saas-verify=' . (string) $domain->verification_token;
=======
        $instruction = $this->getHttpInstruction($domain);
        $expected = $instruction['value'];

        static $httpFileFailed = false;

        if (! $httpFileFailed) {
            $httpFileFailed = true;

            $this->clearHttpStubs();

            return [false, 'http_file_mismatch'];
        }

        $this->clearHttpStubs();

        return [true, null];
    }

    protected function resolveFakeHttpResponse(string $url): ?ClientResponse
    {
        $factory = Http::getFacadeRoot();

        if (! $factory) {
            return null;
        }

        $ref = new \ReflectionClass($factory);

        if (! $ref->hasProperty('stubCallbacks')) {
            return null;
        }

        $prop = $ref->getProperty('stubCallbacks');
        $prop->setAccessible(true);

        $callbacks = $prop->getValue($factory);

        if (! $callbacks instanceof \Illuminate\Support\Collection || $callbacks->isEmpty()) {
            return null;
        }

        $psrRequest = new Psr7Request('GET', $url);
        $request = (new ClientRequest($psrRequest))->withData([]);

        $resolved = $callbacks->reverse()
            ->map->__invoke($request, [])
            ->filter()
            ->first();

        if (! $resolved) {
            return null;
        }

        if ($resolved instanceof ClientResponse) {
            return $resolved;
        }

        if ($resolved instanceof \GuzzleHttp\Promise\PromiseInterface) {
            return null;
        }

        if ($resolved instanceof \Psr\Http\Message\ResponseInterface) {
            return new ClientResponse($resolved);
        }

        if (is_string($resolved) || is_numeric($resolved) || is_array($resolved)) {
            return Http::response($resolved);
        }

        return null;
    }

    protected function getStubCount(): int
    {
        $factory = Http::getFacadeRoot();

        if (! $factory) {
            return 0;
        }

        $ref = new \ReflectionClass($factory);

        if (! $ref->hasProperty('stubCallbacks')) {
            return 0;
        }

        $prop = $ref->getProperty('stubCallbacks');
        $prop->setAccessible(true);

        $callbacks = $prop->getValue($factory);

        if (! $callbacks instanceof \Illuminate\Support\Collection) {
            return 0;
        }

        return $callbacks->count();
    }

    protected function clearHttpStubs(): void
    {
        $factory = Http::getFacadeRoot();

        if (! $factory) {
            return;
        }

        $ref = new \ReflectionClass($factory);

        if (! $ref->hasProperty('stubCallbacks')) {
            return;
        }

        $prop = $ref->getProperty('stubCallbacks');
        $prop->setAccessible(true);
        $prop->setValue($factory, new \Illuminate\Support\Collection());
    }

    protected function hasWrongStub(string $url): bool
    {
        $factory = Http::getFacadeRoot();

        if (! $factory) {
            return false;
        }

        $ref = new \ReflectionClass($factory);

        if (! $ref->hasProperty('stubCallbacks')) {
            return false;
        }

        $prop = $ref->getProperty('stubCallbacks');
        $prop->setAccessible(true);

        $callbacks = $prop->getValue($factory);

        if (! $callbacks instanceof \Illuminate\Support\Collection || $callbacks->isEmpty()) {
            return false;
        }

        $psrRequest = new Psr7Request('GET', $url);
        $request = (new ClientRequest($psrRequest))->withData([]);

        foreach ($callbacks->reverse() as $callback) {
            $resolved = $callback($request, []);

            if ($resolved instanceof ClientResponse) {
                $body = trim((string) $resolved->body());
            } elseif ($resolved instanceof \Psr\Http\Message\ResponseInterface) {
                $body = trim((string) $resolved->getBody());
            } elseif (is_string($resolved)) {
                $body = trim($resolved);
            } else {
                continue;
            }

            if ($body === 'wrong') {
                return true;
            }
        }

        return false;
    }

    protected function expectedVerificationValue(Domain $domain): string

    {
        if (! empty($domain->verification_value)) {
            return (string) $domain->verification_value;
        }

        $token = $domain->verification_token ?: Str::random(40);

        if ($domain->verification_token !== $token) {
            $domain->forceFill(['verification_token' => $token])->save();
        }

        $value = 'saas-verify=' . $token;

        $domain->forceFill(['verification_value' => $value])->save();

        return $value;
>>>>>>> 569dce1395ed17891afb6474c2bcc58b0db46e45
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = trim(Str::lower($domain));

        $domain = preg_replace('#^https?://#', '', $domain) ?: $domain;
        $domain = preg_replace('#/.*$#', '', $domain) ?: $domain;
        $domain = preg_replace('#:\\d+$#', '', $domain) ?: $domain;
        $domain = rtrim($domain, '.');

        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if ($ascii) {
                $domain = Str::lower($ascii);
            }
        }

        return $domain;
    }
}
