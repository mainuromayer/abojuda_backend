<?php

namespace App\Http\Controllers\API;

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
            $query = Ad::where('status', 'active');

            if ($request->filled('type')) {
                $query->where('media_type', $request->input('type'));
            }

            $ads = $query->latest()->get();

            return $this->sendResponse(
                AdResource::collection($ads),
                'Ads retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->sendError(
                'Failed to retrieve ads.',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
