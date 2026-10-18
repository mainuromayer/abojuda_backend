<?php

namespace App\Console\Commands;

use App\Helpers\helpers;
use App\Models\FirebaseToken;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Exception\Messaging\NotFound;

class SendFirebaseTestPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test-push 
                            {token? : The device FCM token to send the test notification to} 
                            {--title=Wasel Canada Test Push : Notification title} 
                            {--message=This is a test notification from Wasel Backend server! : Notification message body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test Firebase FCM push notification to a device token with diagnostics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultToken = 'dSavm_wFSPKYRn9i50Iy1R:APA91bEAHUbDPh3rcYdJb0JYpDs6Hm5V9Q8EvAkLqAj2pv3R9SNLx6TiIXpPsVXSfGfgHcOUUPDUlwbcWIgSpJnarRk6RNvFwNag5e67vuIwt8W1mkvpgGA';
        
        $token = $this->argument('token');

        if (!$token) {
            $dbToken = FirebaseToken::where(function ($q) {
                $q->where('is_active', true)->orWhere('is_active', 1)->orWhere('is_active', '1');
            })->latest()->value('token');
            $token = $dbToken ?: $defaultToken;
        }

        $title   = (string) $this->option('title');
        $message = (string) $this->option('message');

        $credentialsPath = env('FIREBASE_CREDENTIALS');
        if ($credentialsPath && !file_exists($credentialsPath) && file_exists(base_path($credentialsPath))) {
            $credentialsPath = base_path($credentialsPath);
        }
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            $candidates = [
                storage_path('app/firebase/wasel-canada-firebase-adminsdk.json'),
                storage_path('app/public/chat.json'),
                storage_path('app/private/google-services.json'),
            ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    $credentialsPath = $candidate;
                    break;
                }
            }
        }

        $this->info("==================================================");
        $this->info("🚀 Wasel Firebase FCM Push Notification Tester");
        $this->info("==================================================");
        $this->line("Target Token : " . substr($token, 0, 30) . '...' . substr($token, -15));
        $this->line("Title        : " . $title);
        $this->line("Message      : " . $message);
        $this->line("Channel ID   : high_importance_channel");
        $this->line("Credentials  : " . ($credentialsPath ?: 'NOT FOUND'));
        $this->info("==================================================");

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            $this->error("❌ Credentials JSON file not found at " . ($credentialsPath ?? 'null'));
            return Command::FAILURE;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $messaging = $factory->createMessaging();

            $notification = FirebaseNotification::create($title, Str::limit($message, 250));

            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                    'sound' => 'default',
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                    'notification_priority' => 'PRIORITY_HIGH',
                ],
            ]);

            $apnsConfig = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body'  => $message,
                        ],
                        'sound' => 'default',
                        'badge' => 1,
                        'content-available' => 1,
                    ],
                ],
            ]);

            $cloudMessage = CloudMessage::new()
                ->toToken($token)
                ->withNotification($notification)
                ->withAndroidConfig($androidConfig)
                ->withApnsConfig($apnsConfig)
                ->withData([
                    'type'       => 'test',
                    'title'      => $title,
                    'message'    => $message,
                    'sent_at'    => now()->toIso8601String(),
                    'channel_id' => 'high_importance_channel',
                ]);

            $messaging->send($cloudMessage);

            $this->info("✅ SUCCESS! Firebase accepted the notification!");
            $this->info("📱 Notification dispatched with Channel ID 'high_importance_channel' and High Priority.");
            $this->info("Check the physical device for heads-up alert banner & sound.");
            return Command::SUCCESS;
        } catch (NotFound $e) {
            $this->warn("⚠️  Firebase Auth: SUCCESSFUL!");
            $this->warn("⚠️  Target Token Status: UNREGISTERED / NOT FOUND.");
            $this->line("");
            $this->line("Explanation: Google FCM server verified the service account credentials successfully,");
            $this->line("but this specific device token is no longer registered or has expired on the app.");
            $this->line("When the app is re-installed or cache is cleared, Firebase generates a brand new token.");
            $this->line("");
            $this->comment("👉 Action: Ask Azizul to open the newly installed app on his phone and check terminal logs for the latest FCM token, or log in so the app calls /v1/firebase/token/add.");
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error("❌ Push dispatch error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
