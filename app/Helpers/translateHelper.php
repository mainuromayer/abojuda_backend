<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class translateHelper
{
    public static function translateText(string $text, string $lang): ?string
    {
        if (empty($text)) {
            return $text;
        }

        return helpers::translateText($text, $lang);
    }

    public static function translateCached(
        string $key,
        string $text,
        string $lang
    ): string {
        if (empty($text)) {
            return $text;
        }

        if ($lang === 'en' && !preg_match('/\p{Arabic}/u', $text)) {
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

        // Fallback: translate using helpers and cache
        $translated = helpers::translateText($text, $lang);
        if (!empty($translated)) {
            Cache::forever($cacheKey, $translated);
            return $translated;
        }

        return $text;
    }

    public static function translateLocation(
        ?string $province,
        ?string $city,
        string $lang
    ): array {
        if (empty($province) && empty($city)) {
            return [
                'province' => $province,
                'city'     => $city,
            ];
        }

        $locations = config('locations') ?? [];
        $matchedProvince = null;
        $matchedCity = null;

        // 1. Identify English province name
        if (!empty($province)) {
            $province = trim($province);
            foreach (array_keys($locations) as $engProv) {
                if (strcasecmp($province, $engProv) === 0) {
                    $matchedProvince = $engProv;
                    break;
                }
                $arProv = self::translateCached('location.province.' . str_replace(' ', '_', $engProv), $engProv, 'ar');
                if (mb_strtolower($province, 'UTF-8') === mb_strtolower($arProv, 'UTF-8')) {
                    $matchedProvince = $engProv;
                    break;
                }
            }
        }

        // 2. Identify English city name
        if (!empty($city)) {
            $city = trim($city);
            $provincesToSearch = $matchedProvince ? [$matchedProvince => $locations[$matchedProvince] ?? []] : $locations;
            foreach ($provincesToSearch as $engProv => $cities) {
                foreach ($cities as $engCity) {
                    if (strcasecmp($city, $engCity) === 0) {
                        $matchedCity = $engCity;
                        if (!$matchedProvince) {
                            $matchedProvince = $engProv;
                        }
                        break 2;
                    }
                    $cityKey = 'location.city.' . str_replace(' ', '_', $engProv) . '.' . str_replace(' ', '_', $engCity);
                    $arCity = self::translateCached($cityKey, $engCity, 'ar');
                    if (mb_strtolower($city, 'UTF-8') === mb_strtolower($arCity, 'UTF-8')) {
                        $matchedCity = $engCity;
                        if (!$matchedProvince) {
                            $matchedProvince = $engProv;
                        }
                        break 2;
                    }
                }
            }
        }

        // 3. Now translate to target language
        $resultProvince = $province;
        $resultCity = $city;

        if ($matchedProvince) {
            if ($lang === 'ar') {
                $resultProvince = self::translateCached('location.province.' . str_replace(' ', '_', $matchedProvince), $matchedProvince, 'ar');
            } else {
                $resultProvince = $matchedProvince;
            }
        } elseif (!empty($province)) {
            $resultProvince = ($lang === 'ar') ? helpers::translateText($province, 'ar') : helpers::translateText($province, 'en');
        }

        if ($matchedCity && $matchedProvince) {
            if ($lang === 'ar') {
                $cityKey = 'location.city.' . str_replace(' ', '_', $matchedProvince) . '.' . str_replace(' ', '_', $matchedCity);
                $resultCity = self::translateCached($cityKey, $matchedCity, 'ar');
            } else {
                $resultCity = $matchedCity;
            }
        } elseif (!empty($city)) {
            $resultCity = ($lang === 'ar') ? helpers::translateText($city, 'ar') : helpers::translateText($city, 'en');
        }

        return [
            'province' => $resultProvince,
            'city'     => $resultCity,
        ];
    }

    public static function translateJobType(mixed $jobType, string $lang): mixed
    {
        if (empty($jobType)) {
            return $jobType;
        }

        $mapEnToAr = [
            'full time'   => 'دوام كامل',
            'full-time'   => 'دوام كامل',
            'part time'   => 'دوام جزئي',
            'part-time'   => 'دوام جزئي',
            'contract'    => 'عقد',
            'temporary'   => 'مؤقت',
            'internship'  => 'تدريب',
            'remote'      => 'عن بعد',
            'freelance'   => 'عمل حر',
            'commission'  => 'عمولة',
            'volunteer'   => 'تطوع',
            'shift work'  => 'عمل بنظام الورديات',
            'permanent'   => 'دائم',
            'casual'      => 'عمل عرضي',
            'hourly'      => 'بالساعة',
            'daily'       => 'يومي',
            'weekly'      => 'أسبوعي',
            'monthly'     => 'شهري',
        ];

        $mapArToEn = array_flip($mapEnToAr);

        $translateSingle = function ($item) use ($lang, $mapEnToAr, $mapArToEn) {
            if (!is_string($item)) {
                return $item;
            }
            $clean = trim($item);
            $lower = mb_strtolower($clean, 'UTF-8');

            if ($lang === 'ar') {
                if (isset($mapEnToAr[$lower])) {
                    return $mapEnToAr[$lower];
                }
                if (preg_match('/\p{Arabic}/u', $clean)) {
                    return $clean;
                }
                return helpers::translateText($clean, 'ar');
            } else {
                if (isset($mapArToEn[$clean])) {
                    return ucwords($mapArToEn[$clean]);
                }
                if (!preg_match('/\p{Arabic}/u', $clean)) {
                    return $clean;
                }
                return helpers::translateText($clean, 'en');
            }
        };

        if (is_array($jobType)) {
            return array_values(array_map($translateSingle, $jobType));
        }

        return $translateSingle($jobType);
    }

    public static function translateCondition(mixed $condition, string $lang): mixed
    {
        if (empty($condition)) {
            return $condition;
        }

        $mapEnToAr = [
            'new'                      => 'جديد',
            'brand new'                => 'جديد تماماً',
            'used'                     => 'مستعمل',
            'like new'                 => 'كالجديد',
            'refurbished'              => 'مجدد',
            'good'                     => 'جيد',
            'fair'                     => 'مقبول',
            'poor'                     => 'رديء',
            'for parts'                => 'قطع غيار',
            'for parts or not working' => 'قطع غيار أو لا يعمل',
            'open box'                 => 'صندوق مفتوح',
        ];

        $mapArToEn = array_flip($mapEnToAr);

        $translateSingle = function ($item) use ($lang, $mapEnToAr, $mapArToEn) {
            if (!is_string($item)) {
                return $item;
            }
            $clean = trim($item);
            $lower = mb_strtolower($clean, 'UTF-8');

            if ($lang === 'ar') {
                if (isset($mapEnToAr[$lower])) {
                    return $mapEnToAr[$lower];
                }
                if (preg_match('/\p{Arabic}/u', $clean)) {
                    return $clean;
                }
                return helpers::translateText($clean, 'ar');
            } else {
                if (isset($mapArToEn[$clean])) {
                    return ucwords($mapArToEn[$clean]);
                }
                if (!preg_match('/\p{Arabic}/u', $clean)) {
                    return $clean;
                }
                return helpers::translateText($clean, 'en');
            }
        };

        if (is_array($condition)) {
            return array_values(array_map($translateSingle, $condition));
        }

        return $translateSingle($condition);
    }

    public static function translateServiceArea(mixed $serviceArea, string $lang): mixed
    {
        if (empty($serviceArea)) {
            return $serviceArea;
        }

        $mapEnToAr = [
            'residential' => 'سكني',
            'commercial'  => 'تجاري',
            'industrial'  => 'صناعي',
            'corporate'   => 'شركات',
            'rural'       => 'ريفي',
            'urban'       => 'حضري',
        ];

        $mapArToEn = array_flip($mapEnToAr);

        $translateSingle = function ($item) use ($lang, $mapEnToAr, $mapArToEn) {
            if (!is_string($item)) {
                return $item;
            }
            $clean = trim($item);
            $lower = mb_strtolower($clean, 'UTF-8');

            if ($lang === 'ar') {
                if (isset($mapEnToAr[$lower])) {
                    return $mapEnToAr[$lower];
                }
                if (preg_match('/\p{Arabic}/u', $clean)) {
                    return $clean;
                }
                return helpers::translateText($clean, 'ar');
            } else {
                if (isset($mapArToEn[$clean])) {
                    return ucwords($mapArToEn[$clean]);
                }
                if (!preg_match('/\p{Arabic}/u', $clean)) {
                    return $clean;
                }
                return helpers::translateText($clean, 'en');
            }
        };

        if (is_array($serviceArea)) {
            return array_values(array_map($translateSingle, $serviceArea));
        }

        return $translateSingle($serviceArea);
    }

    public static function toEnglish(string $text, string $lang): string
    {
        if ($lang === 'en' || empty($text)) {
            return $text;
        }

        return helpers::translateText($text, 'en');
    }

    public static function toArabic(string $text, string $lang): string
    {
        if ($lang === 'ar' || empty($text)) {
            return $text;
        }

        return helpers::translateText($text, 'ar');
    }
}