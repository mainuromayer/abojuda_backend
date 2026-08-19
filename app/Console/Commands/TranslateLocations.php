<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Helpers\translateHelper;

class TranslateLocations extends Command
{
    protected $signature = 'locations:translate {--lang=ar}';

    protected $description = 'Translate all provinces and cities and store translations';

    public function handle()
    {
        $lang = $this->option('lang');

        if (!in_array($lang, ['ar'])) {
            $this->error('Only Arabic translation is supported.');
            return self::FAILURE;
        }

        $locations = config('locations');

        if (!$locations) {
            $this->error('Locations data not found.');
            return self::FAILURE;
        }

        foreach ($locations as $province => $cities) {

            $provinceKey = 'location.province.' .
                str_replace(' ', '_', $province);

            $this->translateAndStore(
                $provinceKey,
                $province,
                $lang
            );

            foreach ($cities as $city) {

                $cityKey = 'location.city.' .
                    str_replace(' ', '_', $province) .
                    '.' .
                    str_replace(' ', '_', $city);

                $this->translateAndStore(
                    $cityKey,
                    $city,
                    $lang
                );
            }
        }

        $this->info('All location translations completed.');

        return self::SUCCESS;
    }

    private function translateAndStore(
        string $key,
        string $text,
        string $lang
    ): void {
        $exists = DB::table('translations')
            ->where('key', $key)
            ->where('lang', $lang)
            ->exists();

        if ($exists) {
            $this->line("Skipped: {$key}");
            return;
        }

        try {
            $translation = translateHelper::translateText(
                $text,
                $lang
            );

            if (!$translation) {
                $this->error("Failed: {$key}");
                return;
            }

            DB::table('translations')->updateOrInsert(
                [
                    'key' => $key,
                    'lang' => $lang,
                ],
                [
                    'value' => $translation,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            Cache::forever(
                "translation.{$key}.{$lang}",
                $translation
            );

            $this->info(
                "{$text} => {$translation}"
            );

        } catch (\Throwable $e) {
            $this->error(
                "Failed: {$text} - {$e->getMessage()}"
            );
        }
    }
}