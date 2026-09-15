<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * API requests pick their language from `?lang=` or the Accept-Language
     * header; the admin dashboard is Arabic-only and keeps the app default.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*')) {
            return $next($request);
        }

        $supported = config('app.supported_locales');
        $lang = $request->query('lang');

        // getPreferredLanguage understands q-values and regional tags
        // ("en-US,en;q=0.9", "ar-EG") and returns the first supported
        // locale when the header is missing or matches nothing.
        App::setLocale(in_array($lang, $supported, true) ? $lang : $request->getPreferredLanguage($supported));

        $response = $next($request);

        // The same URL returns different content per language, so caches
        // (browser, CDN) must key on the header.
        $response->headers->set('Vary', 'Accept-Language', false);

        return $response;
    }
}
