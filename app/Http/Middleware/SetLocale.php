<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    protected array $supportedLocales = ['pt_BR', 'en', 'es'];

    public function handle(Request $request, Closure $next)
    {
        $locale = null;

        if ($request->has('lang')) {
            $locale = $request->get('lang');
        } elseif ($request->user() && !empty($request->user()->language) && in_array($request->user()->language, $this->supportedLocales)) {
            $locale = $request->user()->language;
        } else {
            $browserLocale = substr($request->getPreferredLanguage() ?? 'pt_BR', 0, 2);
            $locale = match ($browserLocale) {
                'pt' => 'pt_BR',
                'es' => 'es',
                default => 'en',
            };
        }

        if (in_array($locale, $this->supportedLocales)) {
            App::setLocale($locale);
            try {
                $request->session()->put('locale', $locale);
            } catch (\Throwable $e) {
                // Session not available yet (global middleware)
            }
        } else {
            App::setLocale('pt_BR');
        }

        return $next($request);
    }
}
