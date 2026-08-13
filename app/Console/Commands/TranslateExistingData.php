<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslateExistingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate-existing-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all existing categories, dynamic pages, and posts to Spatie multilingual JSON format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting translation of existing database records...');

        // 1. Categories
        $categories = \App\Models\Category::all();
        $this->info("Found {$categories->count()} categories to process.");
        foreach ($categories as $category) {
            foreach ($category->translatable as $field) {
                $this->processTranslationUpdate($category, $field);
            }
            $category->save();
        }
        $this->info('Categories processed successfully.');

        // 2. Dynamic Pages
        $pages = \App\Models\DynamicPage::all();
        $this->info("Found {$pages->count()} dynamic pages to process.");
        foreach ($pages as $page) {
            foreach ($page->translatable as $field) {
                $this->processTranslationUpdate($page, $field);
            }
            $page->save();
        }
        $this->info('Dynamic Pages processed successfully.');

        // 3. Posts
        $posts = \App\Models\Post::all();
        $this->info("Found {$posts->count()} posts to process.");
        foreach ($posts as $post) {
            foreach ($post->translatable as $field) {
                $this->processTranslationUpdate($post, $field);
            }
            $post->save();
        }
        $this->info('Posts processed successfully.');

        // 4. Users
        $users = \App\Models\User::all();
        $this->info("Found {$users->count()} users to process.");
        foreach ($users as $user) {
            foreach ($user->translatable as $field) {
                $this->processTranslationUpdate($user, $field);
            }
            $user->save();
        }
        $this->info('Users processed successfully.');

        // 5. Contact Supports
        $supports = \App\Models\ContactSupport::all();
        $this->info("Found {$supports->count()} support requests to process.");
        foreach ($supports as $support) {
            foreach ($support->translatable as $field) {
                $this->processTranslationUpdate($support, $field);
            }
            $support->save();
        }
        $this->info('Support requests processed successfully.');

        // 6. Ads
        $ads = \App\Models\Ad::all();
        $this->info("Found {$ads->count()} ads to process.");
        foreach ($ads as $ad) {
            foreach ($ad->translatable as $field) {
                $this->processTranslationUpdate($ad, $field);
            }
            $ad->save();
        }
        $this->info('Ads processed successfully.');

        // 7. Messages
        $messages = \App\Models\Message::all();
        $this->info("Found {$messages->count()} messages to process.");
        foreach ($messages as $message) {
            foreach ($message->translatable as $field) {
                $this->processTranslationUpdate($message, $field);
            }
            $message->save();
        }
        $this->info('Messages processed successfully.');

        // 8. Reports
        $reports = \App\Models\Report::all();
        $this->info("Found {$reports->count()} reports to process.");
        foreach ($reports as $report) {
            foreach ($report->translatable as $field) {
                $this->processTranslationUpdate($report, $field);
            }
            $report->save();
        }
        $this->info('Reports processed successfully.');

        // 9. Settings
        $settings = \App\Models\Setting::all();
        $this->info("Found {$settings->count()} settings to process.");
        foreach ($settings as $setting) {
            foreach ($setting->translatable as $field) {
                $this->processTranslationUpdate($setting, $field);
            }
            $setting->save();
        }
        $this->info('Settings processed successfully.');

        // 10. System Notifications
        $notifications = \App\Models\SystemNotification::all();
        $this->info("Found {$notifications->count()} system notifications to process.");
        foreach ($notifications as $notification) {
            foreach ($notification->translatable as $field) {
                $this->processTranslationUpdate($notification, $field);
            }
            $notification->save();
        }
        $this->info('System notifications processed successfully.');

        $this->info('All database records translated successfully!');
    }

    /**
     * Process and fill missing translation for either plain strings or partial JSON strings.
     */
    private function processTranslationUpdate($model, $field)
    {
        $raw = $model->getRawOriginal($field);
        if (!$raw || empty($raw)) {
            return;
        }

        if ($this->isJson($raw)) {
            $translations = json_decode($raw, true);
            $enVal = $translations['en'] ?? null;
            $arVal = $translations['ar'] ?? null;

            if ($enVal && empty($arVal)) {
                $arTrans = \App\Helpers\helpers::translateText($enVal, 'ar');
                $model->setTranslation($field, 'ar', $arTrans ?: $enVal);
            } elseif ($arVal && empty($enVal)) {
                if (preg_match('/\p{Arabic}/u', $arVal)) {
                    $enTrans = \App\Helpers\helpers::translateText($arVal, 'en');
                    $model->setTranslation($field, 'en', $enTrans ?: $arVal);
                    $model->setTranslation($field, 'ar', $arVal);
                } else {
                    $arTrans = \App\Helpers\helpers::translateText($arVal, 'ar');
                    $model->setTranslation($field, 'en', $arVal);
                    $model->setTranslation($field, 'ar', $arTrans ?: $arVal);
                }
            }
        } else {
            // Plain string
            if (preg_match('/\p{Arabic}/u', $raw)) {
                $enTrans = \App\Helpers\helpers::translateText($raw, 'en');
                $model->setTranslation($field, 'en', $enTrans ?: $raw);
                $model->setTranslation($field, 'ar', $raw);
            } else {
                $arabic = \App\Helpers\helpers::translateText($raw, 'ar');
                $model->setTranslation($field, 'en', $raw);
                $model->setTranslation($field, 'ar', $arabic ?: $raw);
            }
        }
    }

    /**
     * Helper to verify if string is valid JSON
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        $decoded = json_decode($string, true);
        return (json_last_error() == JSON_ERROR_NONE) && is_array($decoded);
    }
}
