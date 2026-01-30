<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin panel route'ları için varsayılan locale'i Türkçe yap
        if ($request->is(config('app.admin_url').'/*')) {
            $adminLocale = config('app.admin_locale', 'tr');
            
            // Eğer session'da admin_locale varsa onu kullan, yoksa varsayılanı kullan
            if (!session()->has('admin_locale')) {
                app()->setLocale($adminLocale);
                session()->put('admin_locale', $adminLocale);
            } else {
                app()->setLocale(session()->get('admin_locale'));
            }
        }
        
        return $next($request);
    }
}
