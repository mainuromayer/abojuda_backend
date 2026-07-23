<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DynamicPage\ShowRequest;
use App\Http\Resources\Api\V1\DynamicPage\DynamicPageDetailsResource;
use App\Http\Resources\Api\V1\DynamicPage\DynamicPageResource;
use App\Models\DynamicPage;
use App\Traits\ApiResponse;
use Exception;

class DynamicPageController extends Controller
{
    use ApiResponse;

    /**
     * Get list of all dynamic pages.
     */
    public function index()
    {
        try {
            $pages = DynamicPage::select('id', 'title', 'slug')->get();
            return $this->sendResponse(DynamicPageResource::collection($pages), 'Pages retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve pages.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get details of a dynamic page by slug parameter.
     */
    public function show(ShowRequest $request)
    {
        try {
            $page = DynamicPage::where('slug', $request->slug)->first();
            if (!$page) {
                return $this->sendError('Page not found.', [], 404);
            }
            return $this->sendResponse(new DynamicPageDetailsResource($page), 'Page details retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve page details.', ['error' => $e->getMessage()], 500);
        }
    }
}
