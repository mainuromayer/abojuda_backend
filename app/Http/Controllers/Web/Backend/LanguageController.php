<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, $locale = null)
    {
        $lang = $locale ?: $request->input('locale');

        if (!in_array($lang, ['en', 'ar'])) {
            $lang = 'en';
        }

        Session::put('locale', $lang);
        App::setLocale($lang);
        Cookie::queue(Cookie::make('locale', $lang, 60 * 24 * 365));

        \App\Helpers\helpers::updateEnv('APP_LOCALE', $lang);

        if (auth()->check()) {
            $user = auth()->user();
            if (\Schema::hasColumn('users', 'locale')) {
                $user->locale = $lang;
                $user->save();
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Language updated successfully!'),
                'locale' => $lang,
                'dir' => $lang === 'ar' ? 'rtl' : 'ltr'
            ]);
        }

        return redirect()->back()->with('success', __('Language updated successfully!'));
    }
}
