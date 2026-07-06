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
            
            $factory = (new Factory)->withServiceAccount(storage_path('app/private/google-services.json'));

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

}
