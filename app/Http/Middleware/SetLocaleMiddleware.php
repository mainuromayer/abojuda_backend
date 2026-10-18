<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale', 'ar');
        $locale = $defaultLocale;

        if ($request->is('api/*')) {
            $user = auth('api')->user();
            if (!$user && $request->has('guest_token')) {
                try {
                    $user = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::setToken($request->guest_token)->authenticate();
                } catch (\Exception $e) {
                }
            }

            // 1. Explicit request body or query parameter (supports JSON body, form data, query string)
            $inputLang = strtolower((string) ($request->input('lang') ?? $request->input('locale') ?? ''));

            // 2. Explicit custom HTTP header (e.g. lang: en, locale: en, x-locale: en)
            $headerLang = strtolower((string) ($request->header('lang') ?? $request->header('locale') ?? $request->header('X-Locale') ?? $request->header('X-Language') ?? ''));

            // 3. Standard HTTP Accept-Language header
            $acceptLang = '';
            if ($request->header('Accept-Language')) {
                $acceptLang = strtolower(substr(trim($request->header('Accept-Language')), 0, 2));
            }

            if (in_array($inputLang, ['en', 'ar'])) {
                $locale = $inputLang;
            } elseif (in_array($headerLang, ['en', 'ar'])) {
                $locale = $headerLang;
            } elseif (in_array($acceptLang, ['en', 'ar'])) {
                $locale = $acceptLang;
            } elseif ($user && $user->locale && in_array($user->locale, ['en', 'ar'])) {
                $locale = $user->locale;
            } elseif ($request->hasCookie('locale') && in_array($request->cookie('locale'), ['en', 'ar'])) {
                $locale = $request->cookie('locale');
            } elseif (\Illuminate\Support\Facades\Cache::has('guest_locale_' . $request->ip())) {
                $cached = \Illuminate\Support\Facades\Cache::get('guest_locale_' . $request->ip());
                if (in_array($cached, ['en', 'ar'])) {
                    $locale = $cached;
                }
            }

            $request->headers->set('Accept-Language', $locale);
        } else {
            if ($request->has('lang') && in_array($request->get('lang'), ['en', 'ar'])) {
                $locale = $request->get('lang');
                Session::put('locale', $locale);
            } elseif ($request->has('locale') && in_array($request->get('locale'), ['en', 'ar'])) {
                $locale = $request->get('locale');
                Session::put('locale', $locale);
            } elseif (Session::has('locale') && in_array(Session::get('locale'), ['en', 'ar'])) {
                $locale = Session::get('locale');
            } elseif ($request->hasCookie('locale') && in_array($request->cookie('locale'), ['en', 'ar'])) {
                $locale = $request->cookie('locale');
            } elseif ($request->header('Accept-Language')) {
                $preferredLang = substr($request->header('Accept-Language'), 0, 2);
                if (in_array($preferredLang, ['en', 'ar'])) {
                    $locale = $preferredLang;
                }
            } elseif (($user = auth()->user()) && $user->locale && in_array($user->locale, ['en', 'ar'])) {
                $locale = $user->locale;
            }
        }

        App::setLocale($locale);

        view()->share('currentLocale', $locale);
        view()->share('dir', $locale === 'ar' ? 'rtl' : 'ltr');

        $response = $next($request);

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);

            if (is_array($data) && isset($data['message']) && is_string($data['message'])) {
                $data['message'] = __($data['message']);
                $response->setData($data);
            }
        }

        return $response;
    }
}