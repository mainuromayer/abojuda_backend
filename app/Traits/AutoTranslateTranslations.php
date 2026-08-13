<?php

namespace App\Traits;

use App\Helpers\helpers;

trait AutoTranslateTranslations
{
    /**
     * Boot the trait to listen to the saving event.
     */
    protected static function bootAutoTranslateTranslations()
    {
        static::saving(function ($model) {
            if (!property_exists($model, 'translatable') || !is_array($model->translatable)) {
                return;
            }

            foreach ($model->translatable as $field) {
                $raw = $model->attributes[$field] ?? null;
                if ($raw) {
                    $translations = json_decode($raw, true);
                    if (is_array($translations)) {
                        $enVal = $translations['en'] ?? null;
                        $arVal = $translations['ar'] ?? null;
                        
                        if ($enVal && empty($arVal)) {
                            $arTrans = helpers::translateText($enVal, 'ar');
                            if ($arTrans) {
                                $translations['ar'] = $arTrans;
                            }
                        } elseif ($arVal && empty($enVal)) {
                            if (preg_match('/\p{Arabic}/u', $arVal)) {
                                $enTrans = helpers::translateText($arVal, 'en');
                                if ($enTrans) {
                                    $translations['en'] = $enTrans;
                                }
                            } else {
                                $translations['en'] = $arVal;
                                $arTrans = helpers::translateText($arVal, 'ar');
                                if ($arTrans) {
                                    $translations['ar'] = $arTrans;
                                }
                            }
                        }
                        $model->attributes[$field] = json_encode($translations, JSON_UNESCAPED_UNICODE);
                    } else {
                        // It is a plain string
                        if (preg_match('/\p{Arabic}/u', $raw)) {
                            $enTrans = helpers::translateText($raw, 'en');
                            $translations = [
                                'en' => $enTrans ?: $raw,
                                'ar' => $raw
                            ];
                        } else {
                            $arTrans = helpers::translateText($raw, 'ar');
                            $translations = [
                                'en' => $raw,
                                'ar' => $arTrans ?: $raw
                            ];
                        }
                        $model->attributes[$field] = json_encode($translations, JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        });
    }
}
