<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

class TranslateTextJob implements ShouldQueue
{
    public function __construct(
        public string $key,
        public string $text,
        public string $lang
    ) {}

    public function handle()
    {
        if (DB::table('translations')
            ->where('key', $this->key)
            ->where('lang', $this->lang)
            ->exists()) {
            return;
        }

        try {
            $tr = new GoogleTranslate();
            $tr->setTarget($this->lang);

            $translated = $tr->translate($this->text);

            DB::table('translations')->updateOrInsert(
                ['key' => $this->key, 'lang' => $this->lang],
                [
                    'value'      => $translated,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            \Illuminate\Support\Facades\Cache::put("translation.{$this->key}.{$this->lang}", $translated, 86400);

        } catch (Throwable $e) {
            Log::error('Translation failed', [
                'key' => $this->key,
                'lang' => $this->lang,
            ]);
        }
    }
}
