<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class translateHelper
{
    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    public static function translateText(string $text, string $lang): ?string
    {
        if ($lang === 'en') {
            return $text;
        }

        $tr = new GoogleTranslate();
        $tr->setTarget($lang);

        return $tr->translate($text);
    }

    public static function translateCached(
        string $key,
        string $text,
        string $lang
    ): string {
        if ($lang === 'en' || empty($text)) {
            return $text;
        }

        // Try local translation files (ar.json / en.json) first
        $translatedLocal = __($text, [], $lang);
        if ($translatedLocal !== $text) {
            return $translatedLocal;
        }

        $cacheKey = "translation.{$key}.{$lang}";

        $cached = Cache::get($cacheKey);

        if (!empty($cached)) {
            return $cached;
        }

        $row = DB::table('translations')
            ->where('key', $key)
            ->where('lang', $lang)
            ->first();

        if ($row && !empty($row->value)) {
            Cache::forever($cacheKey, $row->value);

            return $row->value;
        }

        return $text;
    }

    public static function translateLocation(
        ?string $province,
        ?string $city,
        string $lang
    ): array {
        if ($lang === 'en' || (empty($province) && empty($city))) {
            return [
                'province' => $province,
                'city' => $city,
            ];
        }

        $translatedProvince = $province;
        $translatedCity = $city;

        if (!empty($province)) {
            $provinceKey = 'location.province.' . str_replace(' ', '_', $province);
            $translatedProvince = self::translateCached($provinceKey, $province, $lang);
        }

        if (!empty($city) && !empty($province)) {
            $cityKey = 'location.city.' . str_replace(' ', '_', $province) . '.' . str_replace(' ', '_', $city);
            $translatedCity = self::translateCached($cityKey, $city, $lang);
        }

        return [
            'province' => $translatedProvince,
            'city' => $translatedCity,
        ];
    }


    public static function toEnglish(string $text, string $lang): string
    {
        if ($lang === 'en' || empty($text)) {
            return $text;
        }

        $cacheKey = 'translate.to_en.' . $lang . '.' . md5($text);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $tr = new GoogleTranslate();
            $tr->setSource($lang)->setTarget('en');

            $translated = $tr->translate($text);

            Cache::put($cacheKey, $translated, now()->addDays(30));

            return $translated;
        } catch (\Throwable $e) {
            Log::error('toEnglish translation failed', [
                'lang' => $lang,
                'err' => $e->getMessage(),
            ]);

            return $text; // fallback: store original text rather than fail signup
        }
    }

    /**
     * Translate arbitrary text into Arabic. Cached the same way as toEnglish.
     */
    public static function toArabic(string $text, string $lang): string
    {
        if ($lang === 'ar' || empty($text)) {
            return $text;
        }

        $cacheKey = 'translate.to_ar.' . $lang . '.' . md5($text);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $tr = new GoogleTranslate();
            $tr->setSource($lang)->setTarget('ar');

            $translated = $tr->translate($text);

            Cache::put($cacheKey, $translated, now()->addDays(30));

            return $translated;
        } catch (\Throwable $e) {
            Log::error('toArabic translation failed', [
                'lang' => $lang,
                'err' => $e->getMessage(),
            ]);

            return $text; // fallback
        }
    }
}