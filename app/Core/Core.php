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

    protected function fallbackChannelCode(): string
    {
        $firstChannel = $this->channelRepository->first();

        return $firstChannel ? $firstChannel->code : 'default';
    }

    /**
     * Returns current locale with null-safe fallback for admin routes.
     */
    public function getCurrentLocale()
    {
        $locale = parent::getCurrentLocale();

        if (! $locale) {
            // Fallback to default locale if current locale is null (e.g., admin routes without tenant context)
            $defaultLocale = $this->localeRepository->findOneByField('code', config('app.locale'));

            return $defaultLocale ?: $this->localeRepository->first();
        }

        return $locale;
    }
}
