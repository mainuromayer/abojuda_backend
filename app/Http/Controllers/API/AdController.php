<?php

namespace App\Http\Controllers\API;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ad\GetAdsRequest;
use App\Http\Resources\Api\V1\Ad\AdResource;
use App\Models\Ad;
use App\Traits\ApiResponse;
use Exception;

class AdController extends Controller
{
    use ApiResponse;

    /**
     * Get active ads list
     */
    public function index(GetAdsRequest $request)
    {
        try {
            $locale = $request->header('Accept-Language', 'en');
            
            $query = Ad::where('status', 'active');

            if ($request->filled('type')) {
                $query->where('media_type', $request->input('type'));
            }

            $ads = $query->latest()->get();

            $lang = $locale;
            if ($lang !== 'en') {
                $ads->each(function ($ad) use ($lang) {
                    if (!empty($ad->title)) {
                        $ad->title = translateHelper::translateCached("ad_title_{$ad->id}", $ad->title, $lang);
                    }
                    if (!empty($ad->description)) {
                        $ad->description = translateHelper::translateCached("ad_description_{$ad->id}", $ad->description, $lang);
                    }
                });
            }

            $message = translateHelper::translateCached(
                'ad.index.success',
                'Ads retrieved successfully.',
                $locale
            );

            return $this->sendResponse(
                AdResource::collection($ads),
                $message
            );
        } catch (Exception $e) {
            $message = translateHelper::translateCached(
                'ad.index.failed',
                'Failed to retrieve ads.',
                $locale
            );

            return $this->sendError(
                $message,
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}