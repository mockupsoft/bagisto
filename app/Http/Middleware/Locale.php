<?php

namespace App\Http\Middleware;

use Closure;
use Webkul\Core\Repositories\LocaleRepository;
use Webkul\Shop\Http\Middleware\Locale as ShopLocale;

class Locale extends ShopLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = ltrim($request->path(), '/');
        
        // Admin routes bypass - channel may be null
        if (str_starts_with($path, 'admin') || str_starts_with($path, 'super') || str_starts_with($path, 'api/admin')) {
            $channel = core()->getCurrentChannel();
            
            if (! $channel) {
                // Use default locale if channel is null (admin routes)
                app()->setLocale(config('app.locale'));
                return $next($request);
            }
        }
        
        // Original Shop Locale middleware logic with null check
        $channel = core()->getCurrentChannel();
        
        if (! $channel) {
            // Fallback to default locale if channel is null
            app()->setLocale(config('app.locale'));
            return $next($request);
        }
        
        // Additional null checks for channel relationships
        if (! $channel->locales || $channel->locales->isEmpty()) {
            app()->setLocale(config('app.locale'));
            return $next($request);
        }
        
        $locales = $channel->locales->pluck('code')->toArray();
        $localeCode = core()->getRequestedLocaleCode('locale', false);

        if (! $localeCode || ! in_array($localeCode, $locales)) {
            $localeCode = session()->get('locale');
        }

        if (! $localeCode || ! in_array($localeCode, $locales)) {
            // Safe access to default_locale
            if ($channel->default_locale) {
                $localeCode = $channel->default_locale->code;
            } else {
                $localeCode = config('app.locale');
            }
        }

        app()->setLocale($localeCode);
        session()->put('locale', $localeCode);
        unset($request['locale']);

        return $next($request);
    }
}
