<?php

namespace App\Jobs;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Models\Announcement;
use App\Models\FirebaseToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAnnouncementPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $announcementId,
        public string $title,
        public string $description
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        @ignore_user_abort(true);
        @set_time_limit(0);

        $announcement = Announcement::find($this->announcementId);

        // Fetch all active tokens of active users with notifications enabled and active guest tokens
        $tokenModels = FirebaseToken::with('user')
            ->where(function ($q) {
                $q->where('is_active', true)
                  ->orWhere('is_active', 1)
                  ->orWhere('is_active', '1');
            })
            ->where(function ($query) {
                $query->whereNull('user_id')
                      ->orWhereHas('user', function ($u) {
                          $u->where('status', 'active')
                            ->where('enable_notification', '!=', 0);
                      });
            })
            ->get();

        if ($tokenModels->isEmpty()) {
            Log::info("SendAnnouncementPushJob: No active device tokens found for Announcement #{$this->announcementId}.");
            return;
        }

        $titleAr = translateHelper::toArabic($this->title, 'en');
        $titleEn = translateHelper::toEnglish($this->title, 'ar');
        $descAr  = translateHelper::toArabic($this->description, 'en');
        $descEn  = translateHelper::toEnglish($this->description, 'ar');

        $deliveredCount = 0;
        $processedTokens = [];

        foreach ($tokenModels as $tokenModel) {
            $token = $tokenModel->token;
            if (!$token || in_array($token, $processedTokens)) {
                continue;
            }
            $processedTokens[] = $token;

            // Resolve target language based on user profile locale or announcement script
            $userLocale = $tokenModel->user?->locale ?? (preg_match('/\p{Arabic}/u', $this->title) ? 'ar' : 'en');
            $pushTitle = ($userLocale === 'ar' ? $titleAr : $titleEn) ?: $this->title;
            $pushDesc  = ($userLocale === 'ar' ? $descAr : $descEn) ?: $this->description;

            $pushData = [
                'title'      => $pushTitle,
                'message'    => $pushDesc,
                'channel_id' => 'high_importance_channel',
                'data'       => [
                    'type'            => 'announcement',
                    'announcement_id' => (string) $this->announcementId,
                    'title'           => (string) $pushTitle,
                    'description'     => (string) $pushDesc,
                    'title_en'        => (string) $titleEn,
                    'title_ar'        => (string) $titleAr,
                    'message_en'      => (string) $descEn,
                    'message_ar'      => (string) $descAr,
                    'sent_at'         => now()->toIso8601String(),
                    'channel_id'      => 'high_importance_channel',
                ],
            ];

            try {
                helpers::sendNotifyMobile($token, $pushData);
                $deliveredCount++;
                // Gentle delay between each delivery to prevent provider rate-limits
                usleep(30000); // 30ms
            } catch (\Throwable $e) {
                Log::warning("Failed to dispatch push notification to token {$token}: " . $e->getMessage());
            }
        }

        Log::info("SendAnnouncementPushJob completed for Announcement #{$this->announcementId}: {$deliveredCount} devices reached out of " . count($processedTokens) . " unique tokens.");
    }
}
