<?php

namespace App\Http\Controllers\API;

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
            $user = auth()->user();
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
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $postId = $request->input('post_id');

            // Find existing wish
            $wish = Wish::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->first();

            if ($wish) {
                // If it exists, remove it
                $wish->delete();
                return $this->sendResponse(null, 'Post removed from wishlist successfully.');
            }

            // If it doesn't exist, instantiate new Wish object and save it
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
