<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale', 'en');

        if ($request->has('lang') && in_array($request->get('lang'), ['en', 'ar'])) {
            $locale = $request->get('lang');
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
        } elseif (($user = auth()->user() ?: auth('api')->user()) && $user->locale && in_array($user->locale, ['en', 'ar'])) {
            $locale = $user->locale;
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
