<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the page language: ?lang= first, then the fts_lang cookie, then the
 * configured default. Anything that is not a supported language is ignored.
 */
class SetLocale
{
    public const COOKIE = 'fts_lang';

    public function handle(Request $request, Closure $next): Response
    {
        $languages = config('fts.languages');

        $fromQuery = $this->supported($request->query('lang'), $languages);
        $fromCookie = $this->supported($request->cookie(self::COOKIE), $languages);
        $default = $this->supported(config('app.locale'), $languages) ?? 'id';

        app()->setLocale($fromQuery ?? $fromCookie ?? $default);

        $response = $next($request);

        if ($fromQuery !== null) {
            $response->headers->setCookie(cookie(self::COOKIE, $fromQuery, 60 * 24 * 365));
        }

        return $response;
    }

    /** @param  array<int, string>  $languages */
    private function supported(mixed $value, array $languages): ?string
    {
        return is_string($value) && in_array($value, $languages, true) ? $value : null;
    }
}
