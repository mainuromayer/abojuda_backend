<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Get translated attribute based on app locale or specified locale.
     *
     * @param string $attribute
     * @param string|null $locale
     * @param bool $useFallback
     * @return mixed
     */
    public function getTranslation(string $attribute, ?string $locale = null, bool $useFallback = true)
    {
        $locale = $locale ?: App::getLocale();
        $value = parent::getAttribute($attribute);

        if (is_null($value)) {
            return null;
        }

        $translations = $this->decodeTranslation($value);

        if (is_array($translations)) {
            if (isset($translations[$locale]) && $translations[$locale] !== '') {
                return $translations[$locale];
            }

            if ($useFallback) {
                $fallback = config('app.fallback_locale', 'en');
                if (isset($translations[$fallback]) && $translations[$fallback] !== '') {
                    return $translations[$fallback];
                }
                foreach ($translations as $val) {
                    if (!empty($val)) {
                        return $val;
                    }
                }
            }

            return '';
        }

        return $value;
    }

    /**
     * Set translation for specified attribute and locale.
     *
     * @param string $attribute
     * @param string $locale
     * @param string $value
     * @return $this
     */
    public function setTranslation(string $attribute, string $locale, ?string $value)
    {
        $translations = $this->decodeTranslation(parent::getAttribute($attribute)) ?: [];
        $translations[$locale] = $value;
        $this->attributes[$attribute] = json_encode($translations, JSON_UNESCAPED_UNICODE);

        return $this;
    }

    /**
     * Decode JSON translation array safely.
     *
     * @param mixed $value
     * @return array|null
     */
    protected function decodeTranslation($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '['))) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Override getAttribute to automatically translate translatable attributes if defined in $translatable.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (property_exists($this, 'translatable') && is_array($this->translatable) && in_array($key, $this->translatable)) {
            return $this->getTranslation($key);
        }

        return parent::getAttribute($key);
    }
}
