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

            // 1. Explicit request query parameter (e.g. ?lang=ar or ?locale=ar)
            if ($request->has('lang') && in_array($request->get('lang'), ['en', 'ar'])) {
                $locale = $request->get('lang');
            } elseif ($request->has('locale') && in_array($request->get('locale'), ['en', 'ar'])) {
                $locale = $request->get('locale');
            }
            // 2. Explicit custom HTTP header (e.g. lang: ar or locale: ar)
            elseif ($request->header('lang') && in_array($request->header('lang'), ['en', 'ar'])) {
                $locale = $request->header('lang');
            } elseif ($request->header('locale') && in_array($request->header('locale'), ['en', 'ar'])) {
                $locale = $request->header('locale');
            }
            // 3. Standard HTTP Accept-Language header
            elseif ($request->header('Accept-Language')) {
                $preferredLang = substr($request->header('Accept-Language'), 0, 2);
                if (in_array($preferredLang, ['en', 'ar'])) {
                    $locale = $preferredLang;
                } elseif ($user && $user->locale && in_array($user->locale, ['en', 'ar'])) {
                    $locale = $user->locale;
                }
            }
            // 4. Authenticated user's preference in DB
            elseif ($user && $user->locale && in_array($user->locale, ['en', 'ar'])) {
                $locale = $user->locale;
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