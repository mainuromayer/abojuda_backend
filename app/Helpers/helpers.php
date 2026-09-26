<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Throwable;

class helpers
{
    /**
     * File upload
     */
    public static function uploadFile($folderName, $file, $fileName = null, $disk = 'public'): string
    {
        $filePath = 'uploads/' . $folderName . '/' . $fileName;

        if ($disk === 's3') {
            Storage::disk($disk)->put(
                $filePath,
                file_get_contents($file),
                [
                    'visibility' => 'public',
                    'ContentType' => $file->getMimeType(),
                    'ContentDisposition' => 'inline',
                ]
            );
        } else {
            Storage::disk($disk)->putFileAs(
                'uploads/' . $folderName,
                $file,
                $fileName
            );
        }

        return $filePath;
    }

    /**
     *  remove file
     */
    public static function deleteFile($filePath, $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
            return true;
        }
        return false;
    }

    /**
     * generate temp or direct url
     */
    public static function generateTempURL($path, $disk = 'public'): ?string
    {
        if (!$path || !Storage::disk($disk)->exists($path)) {
            return null;
        }

        try {
            return Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes(30),
            );
        } catch (Throwable $e) {
            return Storage::disk($disk)->url($path);
        }
    }

    /**
     * Send firebase notification
     */
    public static function sendNotifyMobile($token, $data): void
    {
        try {
            if (!class_exists(Factory::class)) {
                Log::error('Class "Kreait\\Firebase\\Factory" not found. Check if the Firebase SDK is installed.');
                return;
            }
            
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if (!$credentialsPath || !file_exists($credentialsPath)) {
                if (file_exists(storage_path('app/public/chat.json'))) {
                    $credentialsPath = storage_path('app/public/chat.json');
                } else {
                    $credentialsPath = storage_path('app/private/google-services.json');
                }
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);

            $messaging    = $factory->createMessaging();
            $notification = FirebaseNotification::create($data['title'], Str::limit($data['message'], 100));

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);
                
            if (isset($data['data'])) {
                $message = $message->withData($data['data']);
            }

            $messaging->send($message);
        } catch (\Exception $exception) {
            Log::error('Firebase push notification failed: ' . $exception->getMessage());
        }
    }

    /**
     * Automatically translate text to target locale using Google Translate API.
     */
    public static function translateText(array|string|null $text, string $targetLocale): string
    {
        if (is_array($text)) {
            if (isset($text[$targetLocale]) && !empty($text[$targetLocale])) {
                return $text[$targetLocale];
            }
            $fallbackText = $text['en'] ?? (reset($text) ?: '');
            return self::translateText($fallbackText, $targetLocale);
        }

        if (empty($text) || !in_array($targetLocale, ['en', 'ar'])) {
            return $text ?? '';
        }

        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // If the text contains HTML tags, use the HTML-aware translator
        if (str_contains($text, '<')) {
            return self::translateHtml($text, $targetLocale);
        }

        return self::translateRawText($text, $targetLocale);
    }

    /**
     * Internal raw text translator (no HTML check, safe from recursion)
     */
    public static function translateRawText(string $text, string $targetLocale): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $cacheKey = 'auto_trans_' . $targetLocale . '_' . md5($text);

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        $client = new \GuzzleHttp\Client(['timeout' => 5.0]);

        // Method 1: Google dict endpoint (clients5) - fast, reliable, high capacity
        try {
            $response = $client->get('https://clients5.google.com/translate_a/t', [
                'query' => [
                    'client' => 'dict-chrome-ex',
                    'sl' => 'auto',
                    'tl' => $targetLocale,
                    'q' => $text,
                ],
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if (is_array($data)) {
                $translated = '';
                foreach ($data as $part) {
                    if (is_array($part) && isset($part[0])) {
                        $translated .= $part[0];
                    } elseif (is_string($part)) {
                        $translated .= $part;
                    }
                }
                $finalText = trim($translated);
                if (!empty($finalText)) {
                    \Illuminate\Support\Facades\Cache::forever($cacheKey, $finalText);
                    return $finalText;
                }
            }
        } catch (\Throwable $e) {
            // Fall through to next method
        }

        // Method 2: MyMemory API fallback
        try {
            $srcLang = ($targetLocale === 'ar') ? 'en' : 'ar';
            $response = $client->get('https://api.mymemory.translated.net/get', [
                'query' => [
                    'q' => $text,
                    'langpair' => "{$srcLang}|{$targetLocale}",
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if (is_array($data) && !empty($data['responseData']['translatedText'])) {
                $finalText = trim($data['responseData']['translatedText']);
                \Illuminate\Support\Facades\Cache::forever($cacheKey, $finalText);
                return $finalText;
            }
        } catch (\Throwable $e) {
            // Fall through to next method
        }

        // Method 3: Google translate_a/single endpoint
        try {
            $response = $client->get('https://translate.googleapis.com/translate_a/single', [
                'query' => [
                    'client' => 'gtx',
                    'sl' => 'auto',
                    'tl' => $targetLocale,
                    'dt' => 't',
                    'q' => $text,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if (is_array($data) && isset($data[0])) {
                $translated = '';
                foreach ($data[0] as $part) {
                    if (isset($part[0])) {
                        $translated .= $part[0];
                    }
                }
                $finalText = trim($translated) ?: $text;
                \Illuminate\Support\Facades\Cache::forever($cacheKey, $finalText);
                return $finalText;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Auto translation failed for: ' . $text . ' | Error: ' . $e->getMessage());
        }

        return $text;
    }

    /**
     * Translate HTML content while preserving tags and structure
     */
    public static function translateHtml(string $html, string $targetLocale): string
    {
        if (empty($html)) {
            return '';
        }

        // Split by block tags to get paragraphs/blocks
        $parts = preg_split('/(<\/p>|<\/div>|<\/li>|<br\s*\/?>|<\/h[1-6]>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $translatedParts = [];

        foreach ($parts as $part) {
            if (empty($part)) {
                $translatedParts[] = '';
                continue;
            }

            // If it is just a delimiter/closing tag, don't translate it
            if (preg_match('/^(<\/p>|<\/div>|<\/li>|<br\s*\/?>|<\/h[1-6]>)$/i', $part)) {
                $translatedParts[] = $part;
                continue;
            }

            // Decode HTML entities to check if it contains actual letters/content
            $textDecoded = html_entity_decode(strip_tags($part), ENT_QUOTES, 'UTF-8');
            if (!preg_match('/\p{L}/u', $textDecoded)) {
                // No letters found, keep original block
                $translatedParts[] = $part;
            } else {
                // Clean up bloated style attributes to prevent URL length limits if the part is too long
                $cleanedPart = $part;
                if (strlen($part) > 1000) {
                    $cleanedPart = preg_replace('/style="[^"]*"/i', '', $part);
                    $cleanedPart = preg_replace('/class="[^"]*"/i', '', $cleanedPart);
                }
                
                // Translate the paragraph block using translateRawText (avoids infinite recursion)
                $translatedText = self::translateRawText($cleanedPart, $targetLocale);
                $translatedParts[] = $translatedText;
            }
        }

        return implode('', $translatedParts);
    }

    /**
     * Translate single day business hour data
     */
    public static function translateBusinessHourDay(?array $dayData, string $targetLocale): ?array
    {
        if (empty($dayData) || $targetLocale !== 'ar') {
            return $dayData;
        }

        if (isset($dayData['open']) && is_string($dayData['open'])) {
            $dayData['open'] = self::translateText($dayData['open'], 'ar');
        }
        if (isset($dayData['close']) && is_string($dayData['close'])) {
            $dayData['close'] = self::translateText($dayData['close'], 'ar');
        }
        if (isset($dayData['opening_hour']) && is_string($dayData['opening_hour'])) {
            $dayData['opening_hour'] = self::translateText($dayData['opening_hour'], 'ar');
        }
        if (isset($dayData['closing_time']) && is_string($dayData['closing_time'])) {
            $dayData['closing_time'] = self::translateText($dayData['closing_time'], 'ar');
        }
        return $dayData;
    }

    /**
     * Translate entire business hours array
     */
    public static function translateBusinessHours(?array $hours, string $targetLocale): ?array
    {
        if (empty($hours) || $targetLocale !== 'ar') {
            return $hours;
        }

        foreach ($hours as $day => &$data) {
            if (is_array($data)) {
                $data = self::translateBusinessHourDay($data, $targetLocale);
            }
        }
        return $hours;
    }

    /**
     * Update environment variable in .env file dynamically
     */
    public static function updateEnv(string $key, string $value): bool
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                return false;
            }

            $content = file_get_contents($envPath);
            $pattern = "/^{$key}=.*/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }

            file_put_contents($envPath, $content);
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update env key ' . $key . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Translate date string to Arabic
     */
    public static function translateDate($date, string $format = 'h:i a, Y M d'): string
    {
        if (!$date) {
            return '';
        }

        if (app()->getLocale() === 'ar') {
            $dateStr = $date->locale('ar')->translatedFormat($format);
            return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], $dateStr);
        }

        return $date->format($format);
    }
}
