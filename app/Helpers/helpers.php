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

        $cacheKey = 'auto_trans_' . $targetLocale . '_' . md5($text);

        return \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($text, $targetLocale) {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get('https://translate.googleapis.com/translate_a/single', [
                    'query' => [
                        'client' => 'gtx',
                        'sl' => 'auto',
                        'tl' => $targetLocale,
                        'dt' => 't',
                        'q' => $text,
                    ],
                    'timeout' => 5.0,
                ]);

                $data = json_decode($response->getBody(), true);
                if (is_array($data) && isset($data[0])) {
                    $translated = '';
                    foreach ($data[0] as $part) {
                        if (isset($part[0])) {
                            $translated .= $part[0];
                        }
                    }
                    return $translated ?: $text;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Auto translation failed for: ' . $text . ' | Error: ' . $e->getMessage());
            }

            return $text;
        });
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
}
