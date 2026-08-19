<?php

namespace App\Http\Controllers\API;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Wish\StoreRequest;
use App\Http\Requests\Api\V1\Wish\IndexRequest;
use App\Http\Resources\Api\V1\Wish\WishResource;
use App\Http\Resources\Api\V1\Post\PostCardResource;
use App\Models\Post;
use App\Models\Wish;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class WishController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the user's wishes.
     */
    public function index(IndexRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $postsQuery = Post::whereHas('wishes', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

            $blockedUserIds = $user->getBlockedUserIds();
            if (!empty($blockedUserIds)) {
                $postsQuery->whereNotIn('user_id', $blockedUserIds);
            }

            if ($request->filled('category_id')) {
                $postsQuery->where('category_id', $request->input('category_id'));
            }

            $posts = $postsQuery->get();

            $lang = $request->header('Accept-Language', 'en');
            if ($lang !== 'en') {
                $posts->each(function ($post) use ($lang) {
                    if (!empty($post->title)) {
                        $post->title = translateHelper::translateCached("post_title_{$post->id}", $post->title, $lang);
                    }
                    if (!empty($post->business_name)) {
                        $post->business_name = translateHelper::translateCached("post_business_name_{$post->id}", $post->business_name, $lang);
                    }
                });
            }

            return $this->sendResponse(PostCardResource::collection($posts), 'Wishlist retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve wishlist.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle a wish in storage (add if not exists, remove if exists).
     */
    public function toggle(StoreRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $postId = $request->input('post_id');

            $wish = Wish::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->first();

            if ($wish) {
                $wish->delete();
                return $this->sendResponse(null, 'Post removed from wishlist successfully.');
            }

            $wish = new Wish();
            $wish->user_id = $user->id;
            $wish->post_id = $postId;
            $wish->save();

            return $this->sendResponse(null, 'Post added to wishlist successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to toggle wishlist item.', ['error' => $e->getMessage()], 500);
        }
    }
}