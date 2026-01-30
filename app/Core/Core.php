<?php

namespace App\Core;

use App\Services\Tenant\TenantChannelResolver;
use App\Support\Tenant\TenantRequest;
use Webkul\Core\Core as BaseCore;

class Core extends BaseCore
{
    /**
     * Returns current channel code with tenant-safe fallback.
     */
    public function getCurrentChannelCode(): string
    {
        $request = request();

        if ($request && TenantRequest::isTenantResolved() && ! TenantRequest::isAdminPath($request)) {
            $resolver = app(TenantChannelResolver::class);
            $code = $resolver->resolveChannelCodeForRequest($request)
                ?? $resolver->getDefaultChannelCode()
                ?? $this->fallbackChannelCode();

            return $code;
        }

        $channel = $this->getCurrentChannel();

        if ($channel) {
            return $channel->code;
        }

        return $this->fallbackChannelCode();
    }

    /**
     * Returns the default channel code configured in `config/app.php`.
     */
    public function getDefaultChannelCode(): string
    {
        $defaultChannel = $this->getDefaultChannel();
        if ($defaultChannel) {
            return $defaultChannel->code;
        }

        return $this->fallbackChannelCode();
    }

    /**
     * Returns current locale with tenant-safe fallback.
     *
     * @return \Webkul\Core\Contracts\Locale
     */
    public function getCurrentLocale()
    {
        $locale = parent::getCurrentLocale();

        if ($locale) {
            return $locale;
        }

        // Fallback: Create a minimal locale object if none exists
        return $this->fallbackLocale();
    }

    protected function fallbackChannelCode(): string
    {
        $firstChannel = $this->channelRepository->first();

        return $firstChannel ? $firstChannel->code : 'default';
    }

    protected function fallbackLocale()
    {
        // Try to get first available locale
        $firstLocale = $this->localeRepository->first();

        if ($firstLocale) {
            return $firstLocale;
        }

        // Last resort: Create a minimal locale model instance
        $localeCode = app()->getLocale() ?: config('app.fallback_locale', 'en');

        return \Webkul\Core\Models\Locale::make([
            'code' => $localeCode,
            'name' => ucfirst($localeCode),
            'direction' => 'ltr',
        ]);
    }
}
