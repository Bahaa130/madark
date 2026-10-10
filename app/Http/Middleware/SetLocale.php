<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const COOKIE = 'madarek_locale';

    public const SUPPORTED = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        view()->share('dir', config('madarek.direction.' . $locale, 'ltr'));
        $request->attributes->set('locale', $locale);

        $response = $next($request);

        // RTL for Arabic, LTR for English.
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        // 1) explicit ?lang= override
        $query = $request->query('lang');
        if (in_array($query, self::SUPPORTED, true)) {
            cookie()->queue('madarek_locale', $query, 60 * 24 * 365);

            return $query;
        }

        // 2) persisted cookie (the language switcher writes it)
        $cookie = $request->cookie(self::COOKIE);
        if (in_array($cookie, self::SUPPORTED, true)) {
            return $cookie;
        }

        // 3) browser Accept-Language
        $header = $request->header('Accept-Language');
        if ($header) {
            foreach (explode(',', $header) as $part) {
                $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
                if (in_array($code, self::SUPPORTED, true)) {
                    return $code;
                }
            }
        }

        // 4) default
        return config('app.locale') === 'en' ? 'en' : 'ar';
    }
}