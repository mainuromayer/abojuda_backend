<?php

namespace App\Http\Controllers\API;

use App\Enum\Role;
use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Post\AuthPostIndexRequest;
use App\Http\Requests\Api\V1\Post\CreateRequest;
use App\Http\Requests\Api\V1\Post\DeletePostRequest;
use App\Http\Requests\Api\V1\Post\IndexRequest;
use App\Http\Requests\Api\V1\Post\RelatedPostsRequest;
use App\Http\Requests\Api\V1\Post\SaveDraftRequest;
use App\Http\Requests\Api\V1\Post\ShowRequest;
use App\Http\Requests\Api\V1\Post\UpdatePostRequest;
use App\Http\Resources\Api\V1\Category\CategoriesResource;
use App\Http\Resources\Api\V1\Post\AuthPostResource;
use App\Http\Resources\Api\V1\Post\PostCardResource;
use App\Http\Resources\Api\V1\Post\PostResource;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostHistory;
use App\Models\Wish;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Actions\GeoLocation;

class PostController extends Controller
{
    use ApiResponse;

    private $geoLocation;

    public function __construct(GeoLocation $geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    public function index(IndexRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $query = Post::where('status', 'active')->with('category');
            $this->applyFilters($query, $request);

            $user = auth('api')->user();
            if ($user && $user->latitude && $user->longitude) {
                $latitude = $user->latitude;
                $longitude = $user->longitude;

                $query->selectRaw("posts.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude]);

                if (!$request->filled('sort_by')) {
                    $query->orderBy('distance', 'asc');
                }
            } else {
                if (!$request->filled('sort_by')) {
                    $query->latest();
                }
            }

            $posts = $query->paginate($request->input('per_page', 10));

            $message = translateHelper::translateCached('post.index.success', 'Posts retrieved successfully.', $locale);

            return $this->sendResponse(PostCardResource::collection($posts), $message, 200, null, $posts);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.index.failed', 'Failed to retrieve posts.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function show(ShowRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $query = Post::where('id', $request->post_id)->where('status', 'active');

            $user = auth('api')->user();
            if ($user) {
                $blockedUserIds = $user->getBlockedUserIds();
                if (!empty($blockedUserIds)) {
                    $query->whereNotIn('user_id', $blockedUserIds);
                }
            }

            $post = $query->with(['category', 'businessHours'])->first();
            if (!$post) {
                $message = translateHelper::translateCached('post.show.not_found', 'Post not found.', $locale);
                return $this->sendError($message, [], 404);
            }

            $post->increment('views');

            $message = translateHelper::translateCached('post.show.success', 'Post retrieved successfully.', $locale);

            return $this->sendResponse(new PostResource($post), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.show.failed', 'Failed to retrieve post.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function related(RelatedPostsRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $currentPostId = $request->current_post_id;
            $currentPost = Post::findOrFail($currentPostId);

            $query = Post::where('status', 'active')
                ->with('category')
                ->where('category_id', $currentPost->category_id)
                ->where('id', '!=', $currentPostId);

            $user = auth('api')->user();
            if ($user) {
                $blockedUserIds = $user->getBlockedUserIds();
                if (!empty($blockedUserIds)) {
                    $query->whereNotIn('user_id', $blockedUserIds);
                }
            }

            $posts = $query->latest()->take(10)->get();

            $message = translateHelper::translateCached('post.related.success', 'Related posts retrieved successfully.', $locale);

            return $this->sendResponse(PostCardResource::collection($posts), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.related.failed', 'Failed to retrieve related posts.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function recent(IndexRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $query = Post::where('status', 'active')->with('category');
            $this->applyFilters($query, $request);

            $posts = $query->orderBy('updated_at', 'desc')->take(10)->get();

            $message = translateHelper::translateCached('post.recent.success', 'Recent posts retrieved successfully.', $locale);

            return $this->sendResponse(PostCardResource::collection($posts), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.recent.failed', 'Failed to retrieve posts.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function featured(IndexRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $query = Post::where('status', 'active')->where('is_featured', true)->with('category');
            $this->applyFilters($query, $request);

            if (!$request->filled('sort_by')) {
                $query->latest();
            }

            $posts = $query->paginate($request->input('per_page', 10));

            $message = translateHelper::translateCached('post.featured.success', 'Posts retrieved successfully.', $locale);

            return $this->sendResponse(PostCardResource::collection($posts), $message, 200, null, $posts);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.featured.failed', 'Failed to retrieve posts.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function userPosts(AuthPostIndexRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('post.unauthenticated', 'Unauthenticated user.', $locale);
                return $this->sendError($message, [], 401);
            }

            $query = Post::where('user_id', $user->id);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            $posts = $query->latest()->paginate($request->input('per_page', 10));

            $totalPost = Post::where('user_id', $user->id)->count();
            $totalView = (int) Post::where('user_id', $user->id)->sum('views');
            $totalWish = (int) Wish::whereIn('post_id', Post::where('user_id', $user->id)->select('id'))->count();
            $totalMessage = 0; // Default to 0 since no chat/message tables exist yet

            $message = translateHelper::translateCached('post.user_posts.success', 'User posts retrieved successfully.', $locale);

            return $this->sendResponse([
                'total_post' => $totalPost,
                'total_view' => $totalView,
                'total_wish' => $totalWish,
                'total_message' => $totalMessage,
                'posts' => AuthPostResource::collection($posts),
            ], $message, 200, null, $posts);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.user_posts.failed', 'Failed to retrieve user posts.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function deletePost(DeletePostRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('post.unauthenticated', 'Unauthenticated user.', $locale);
                return $this->sendError($message, [], 401);
            }

            $post = Post::where('id', $request->post_id)->where('user_id', $user->id)->first();

            if (!$post) {
                $message = translateHelper::translateCached('post.delete.not_found', 'Post not found or unauthorized.', $locale);
                return $this->sendError($message, [], 404);
            }

            if ($post->thumbnail) {
                helpers::deleteFile($post->thumbnail, config('app.file_system'));
            }
            if ($post->logo) {
                helpers::deleteFile($post->logo, config('app.file_system'));
            }
            if (is_array($post->photos)) {
                foreach ($post->photos as $path) {
                    helpers::deleteFile($path, config('app.file_system'));
                }
            }

            $post->delete();

            $message = translateHelper::translateCached('post.delete.success', 'Post deleted successfully.', $locale);

            return $this->sendResponse([], $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.delete.failed', 'Failed to delete post.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to apply common post filters
     */
    private function applyFilters($query, IndexRequest $request)
    {
        $user = auth('api')->user();
        if ($user) {
            $blockedUserIds = $user->getBlockedUserIds();
            if (!empty($blockedUserIds)) {
                $query->whereNotIn('user_id', $blockedUserIds);
            }
        }

        if ($request->filled('category_slug')) {
            $category = Category::where('slug', $request->input('category_slug'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->filled('province')) {
            $query->where('province', $request->input('province'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('company_name', 'like', '%' . $search . '%')
                    ->orWhere('business_name', 'like', '%' . $search . '%')
                    ->orWhere('business_category', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('sort_by')) {
            switch ($request->input('sort_by')) {
                case 'featured':
                    $query->orderBy('is_featured', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'price_low_high':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high_low':
                    $query->orderBy('price', 'desc');
                    break;
            }
        }

        return $query;
    }


    public function categories(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $categories = Category::where('status', 'active')->get();

            $message = translateHelper::translateCached('post.categories.success', 'Categories retrieved successfully.', $locale);

            return $this->sendResponse(CategoriesResource::collection($categories), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.categories.failed', 'Failed to retrieve categories.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function saveDraft(SaveDraftRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('post.unauthenticated', 'Unauthenticated user.', $locale);
                return $this->sendError($message, [], 401);
            }

            if ($user->role === Role::GUEST) {
                $message = translateHelper::translateCached('post.draft.guest_forbidden', 'Guest users cannot save drafts or create posts.', $locale);
                return $this->sendError($message, [], 403);
            }

            $existingDraftId = Cache::get('user_latest_draft_' . $user->id);

            if ($existingDraftId) {
                $draftKey = 'post_draft_' . $existingDraftId;
                $draftData = Cache::get($draftKey);

                if ($draftData) {
                    $uploadedPhotos = [];
                    if ($request->hasFile('photos')) {
                        foreach ($request->file('photos') as $index => $file) {
                            $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                            $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                            $uploadedPhotos[] = $path;
                        }
                    }

                    $category = null;
                    if ($request->filled('category_slug')) {
                        $category = Category::where('slug', $request->category_slug)->first();
                    } elseif ($request->filled('category_id')) {
                        $category = Category::find($request->category_id);
                    }
                    $categoryId = $category?->id;
                    $categoryName = $category?->name ?? '';
                    $categorySlug = $category?->slug ?? '';

                    $thumbnailPath = null;
                    if ($request->hasFile('thumbnail')) {
                        $thumbnailName = time() . '_thumb_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
                        $thumbnailPath = helpers::uploadFile('posts', $request->file('thumbnail'), $thumbnailName, config('app.file_system'));
                    } elseif (!empty($uploadedPhotos)) {
                        $thumbnailPath = $uploadedPhotos[0];
                    }

                    $logoPath = null;
                    if ($request->hasFile('logo')) {
                        $logoName = time() . '_logo_' . Str::random(10) . '.' . $request->file('logo')->getClientOriginalExtension();
                        $logoPath = helpers::uploadFile('posts', $request->file('logo'), $logoName, config('app.file_system'));
                    }

                    $draftData = [
                        'user_id' => $user->id,
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'category_slug' => $categorySlug,
                        'title' => $request->title ?? null,
                        'company_name' => $request->company_name ?? null,
                        'business_name' => $request->business_name ?? null,
                        'business_category' => $request->business_category ?? null,
                        'description' => $request->description ?? null,
                        'price' => $request->price ?? 0.00,
                        'condition' => $request->condition ?? null,
                        'job_type' => $request->job_type ?? null,
                        'service_area' => $request->service_area ?? null,
                        'province' => $request->province ?? null,
                        'city' => $request->city ?? null,
                        'address' => $request->address ?? null,
                        'phone' => $request->phone ?? null,
                        'whatsapp' => $request->whatsapp ?? null,
                        'email' => $request->email ?? null,
                        'website' => $request->website ?? null,
                        'is_app_chat' => $request->boolean('is_app_chat'),
                        'photos' => $uploadedPhotos,
                        'thumbnail' => $thumbnailPath,
                        'logo' => $logoPath,
                        'status' => 'draft',
                        'business_hours' => $request->business_hours ?? null,
                        'created_at' => $draftData['created_at'] ?? now()->toIso8601String(),
                    ];

                    if ($categorySlug === 'buy-sell') {
                        $draftData['company_name'] = null;
                        $draftData['business_name'] = null;
                        $draftData['business_category'] = null;
                        $draftData['job_type'] = null;
                        $draftData['service_area'] = null;
                        $draftData['website'] = null;
                        $draftData['logo'] = null;
                        $draftData['business_hours'] = null;
                    } elseif ($categorySlug === 'jobs') {
                        $draftData['business_name'] = null;
                        $draftData['business_category'] = null;
                        $draftData['price'] = 0.00;
                        $draftData['condition'] = null;
                        $draftData['service_area'] = null;
                        $draftData['website'] = null;
                        $draftData['logo'] = null;
                        $draftData['business_hours'] = null;
                    } elseif ($categorySlug === 'business-directory') {
                        $draftData['title'] = null;
                        $draftData['company_name'] = null;
                        $draftData['price'] = 0.00;
                        $draftData['condition'] = null;
                        $draftData['job_type'] = null;
                        $draftData['service_area'] = null;
                    } elseif ($categorySlug === 'services') {
                        $draftData['company_name'] = null;
                        $draftData['business_name'] = null;
                        $draftData['business_category'] = null;
                        $draftData['price'] = 0.00;
                        $draftData['condition'] = null;
                        $draftData['job_type'] = null;
                        $draftData['website'] = null;
                        $draftData['logo'] = null;
                        $draftData['business_hours'] = null;
                    }

                    Cache::put($draftKey, $draftData, now()->addDays(7));

                    $message = translateHelper::translateCached('post.draft.update_success', 'Draft updated successfully.', $locale);

                    return $this->sendResponse($this->resolvePhotosForResponse($draftData), $message);
                }
            }

            $uploadedPhotos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $file) {
                    $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                    $uploadedPhotos[] = $path;
                }
            }

            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailName = time() . '_thumb_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
                $thumbnailPath = helpers::uploadFile('posts', $request->file('thumbnail'), $thumbnailName, config('app.file_system'));
            } elseif (!empty($uploadedPhotos)) {
                $thumbnailPath = $uploadedPhotos[0];
            }

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoName = time() . '_logo_' . Str::random(10) . '.' . $request->file('logo')->getClientOriginalExtension();
                $logoPath = helpers::uploadFile('posts', $request->file('logo'), $logoName, config('app.file_system'));
            }

            $draftId = (int) Cache::get('post_draft_counter', 0) + 1;
            Cache::forever('post_draft_counter', $draftId);

            $category = null;
            if ($request->has('category_slug') && !empty($request->category_slug)) {
                $category = Category::where('slug', $request->category_slug)->first();
            } elseif ($request->has('category_id') && !empty($request->category_id)) {
                $category = Category::find($request->category_id);
            }
            $categoryId = $category?->id;
            $categoryName = $category?->name ?? '';
            $categorySlug = $category?->slug ?? '';
            $draftData = [
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'title' => $request->title,
                'company_name' => $request->company_name,
                'business_name' => $request->business_name,
                'business_category' => $request->business_category,
                'description' => $request->description,
                'price' => $request->price ?? 0.00,
                'condition' => $request->condition ?? null,
                'job_type' => $request->job_type,
                'service_area' => $request->service_area,

                'province' => $request->province,
                'city' => $request->city,
                'address' => $request->address,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'email' => $request->email,
                'website' => $request->website,
                'is_app_chat' => $request->boolean('is_app_chat'),
                'photos' => $uploadedPhotos,
                'thumbnail' => $thumbnailPath,
                'logo' => $logoPath,
                'status' => 'draft',
                'business_hours' => $request->business_hours ?? null,
                'created_at' => now()->toIso8601String(),
            ];

            if ($categorySlug === 'buy-sell') {
                $draftData['company_name'] = null;
                $draftData['business_name'] = null;
                $draftData['business_category'] = null;
                $draftData['job_type'] = null;
                $draftData['service_area'] = null;
                $draftData['website'] = null;
                $draftData['logo'] = null;
                $draftData['business_hours'] = null;
            } elseif ($categorySlug === 'jobs') {
                $draftData['business_name'] = null;
                $draftData['business_category'] = null;
                $draftData['price'] = 0.00;
                $draftData['condition'] = null;
                $draftData['service_area'] = null;
                $draftData['website'] = null;
                $draftData['logo'] = null;
                $draftData['business_hours'] = null;
            } elseif ($categorySlug === 'business-directory') {
                $draftData['title'] = null;
                $draftData['company_name'] = null;
                $draftData['price'] = 0.00;
                $draftData['condition'] = null;
                $draftData['job_type'] = null;
                $draftData['service_area'] = null;
            } elseif ($categorySlug === 'services') {
                $draftData['company_name'] = null;
                $draftData['business_name'] = null;
                $draftData['business_category'] = null;
                $draftData['price'] = 0.00;
                $draftData['condition'] = null;
                $draftData['job_type'] = null;
                $draftData['website'] = null;
                $draftData['logo'] = null;
                $draftData['business_hours'] = null;
            }

            Cache::put('post_draft_' . $draftId, $draftData, now()->addDays(7));
            Cache::put('user_latest_draft_' . $user->id, $draftId, now()->addDays(7));

            $message = translateHelper::translateCached('post.draft.create_success', 'Draft created successfully.', $locale);

            return $this->sendResponse($this->resolvePhotosForResponse($draftData), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.draft.save_failed', 'Failed to save draft.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function getDraft(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('post.unauthenticated', 'Unauthenticated user.', $locale);
                return $this->sendError($message, [], 401);
            }

            $existingDraftId = Cache::get('user_latest_draft_' . $user->id);
            if (!$existingDraftId) {
                $message = translateHelper::translateCached('post.draft.none_found', 'No draft found.', $locale);
                return $this->sendError($message, [], 404);
            }

            $draftData = Cache::get('post_draft_' . $existingDraftId);
            if (!$draftData) {
                $message = translateHelper::translateCached('post.draft.data_not_found', 'Draft not found.', $locale);
                return $this->sendError($message, [], 404);
            }

            $message = translateHelper::translateCached('post.draft.get_success', 'Draft retrieved successfully.', $locale);

            return $this->sendResponse($this->resolvePhotosForResponse($draftData), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.draft.get_failed', 'Failed to retrieve draft.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('post.unauthenticated', 'Unauthenticated user.', $locale);
                return $this->sendError($message, [], 401);
            }

            if ($user->role === Role::GUEST) {
                $message = translateHelper::translateCached('post.store.guest_forbidden', 'Guest users cannot create posts.', $locale);
                return $this->sendError($message, [], 403);
            }

            $draftId = Cache::get('user_latest_draft_' . $user->id);
            if (!$draftId) {
                $message = translateHelper::translateCached('post.store.no_draft', 'No draft found to publish.', $locale);
                return $this->sendError($message, [], 404);
            }

            $draftKey = 'post_draft_' . $draftId;
            $draftData = Cache::get($draftKey);
            if (!$draftData) {
                $message = translateHelper::translateCached('post.store.draft_data_not_found', 'Draft data not found.', $locale);
                return $this->sendError($message, [], 404);
            }

            if ($draftData['user_id'] !== $user->id) {
                $message = translateHelper::translateCached('post.store.unauthorized', 'Unauthorized.', $locale);
                return $this->sendError($message, [], 403);
            }

            $categoryId = $draftData['category_id'] ?? null;
            if (!$categoryId) {
                $message = translateHelper::translateCached('post.store.missing_category', 'Missing required category_id in draft.', $locale);
                return $this->sendError($message, [], 422);
            }

            $uploadedPhotos = $draftData['photos'] ?? [];

            $post = new Post();
            $post->user_id = $user->id;
            $post->category_id = $categoryId;

            $categorySlug = $draftData['category_slug'] ?? '';
            if ($categorySlug === 'buy-sell') {
                $post->title = $draftData['title'] ?? null;
                $post->description = $draftData['description'] ?? null;
                $post->price = $draftData['price'] ?? 0.00;
                $post->condition = $draftData['condition'] ?? null;
            } elseif ($categorySlug === 'jobs') {
                $post->title = $draftData['title'] ?? null;
                $post->company_name = $draftData['company_name'] ?? null;
                $post->description = $draftData['description'] ?? null;
                $post->job_type = $draftData['job_type'] ?? null;
            } elseif ($categorySlug === 'business-directory') {
                $post->business_name = $draftData['business_name'] ?? null;
                $post->business_category = $draftData['business_category'] ?? null;
                $post->description = $draftData['description'] ?? null;
                $post->website = $draftData['website'] ?? null;
            } elseif ($categorySlug === 'services') {
                $post->title = $draftData['title'] ?? null;
                $post->description = $draftData['description'] ?? null;
                $post->service_area = $draftData['service_area'] ?? null;
            }

            $post->province = $draftData['province'] ?? null;
            $post->city = $draftData['city'] ?? null;
            $post->address = $draftData['address'] ?? null;

            // Resolve coordinates via GeoLocation
            $postAddress = implode(', ', array_filter([$post->address, $post->city, $post->province]));
            $coords = $this->geoLocation->getCoordinates($postAddress);
            $post->latitude = $coords['latitude'] ?? null;
            $post->longitude = $coords['longitude'] ?? null;

            $post->phone = $draftData['phone'] ?? null;
            $post->whatsapp = $draftData['whatsapp'] ?? null;
            $post->email = $draftData['email'] ?? null;
            $post->is_app_chat = $draftData['is_app_chat'] ?? false;
            $post->photos = $uploadedPhotos;

            $thumbnail = $draftData['thumbnail'] ?? null;
            if (empty($thumbnail) && !empty($uploadedPhotos)) {
                $thumbnail = $uploadedPhotos[0];
            }
            $post->thumbnail = $thumbnail;
            $post->logo = ($categorySlug === 'business-directory') ? ($draftData['logo'] ?? null) : null;
            $post->status = 'pending';
            $post->save();

            $businessHours = ($categorySlug === 'business-directory') ? ($draftData['business_hours'] ?? null) : null;
            if ($businessHours && is_array($businessHours)) {
                $normalizedHours = [];
                foreach ($businessHours as $key => $value) {
                    $normalizedHours[strtolower(trim($key))] = $value;
                }

                $bh = new BusinessHour();
                $bh->post_id = $post->id;
                $bh->sunday = $normalizedHours['sunday'] ?? null;
                $bh->monday = $normalizedHours['monday'] ?? null;
                $bh->tuesday = $normalizedHours['tuesday'] ?? null;
                $bh->wednesday = $normalizedHours['wednesday'] ?? null;
                $bh->thursday = $normalizedHours['thursday'] ?? null;
                $bh->friday = $normalizedHours['friday'] ?? null;
                $bh->saturday = $normalizedHours['saturday'] ?? null;
                $bh->save();
            }

            Cache::forget($draftKey);
            Cache::forget('user_latest_draft_' . $user->id);

            $message = translateHelper::translateCached('post.store.success', 'Listing created successfully.', $locale);

            return $this->sendResponse(new PostResource($post->load(['category', 'businessHours'])), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.store.failed', 'Failed to create listing.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    private function resolvePhotosForResponse(array $draftData): array
    {
        $resolvedPhotos = [];
        if (isset($draftData['photos']) && is_array($draftData['photos'])) {
            foreach ($draftData['photos'] as $path) {
                $resolvedPhotos[] = $this->getImageUrl($path);
            }
        }

        $user = auth()->user() ?? auth('api')->user();

        $categoryName = $draftData['category_name'] ?? '';
        $categoryId = $draftData['category_id'] ?? null;
        $categorySlug = $draftData['category_slug'] ?? '';
        $categoryLower = strtolower($categoryName);

        $createdAt = $draftData['created_at'] ?? null;
        $timeAgo = null;
        if ($createdAt) {
            try {
                $timeAgo = \Carbon\Carbon::parse($createdAt)->diffForHumans();
            } catch (Exception $e) {
                // Ignore parsing errors
            }
        }

        // Auto-translate translatable fields for the draft response if locale is 'ar'
        $title = $draftData['title'] ?? null;
        $description = $draftData['description'] ?? null;
        $companyName = $draftData['company_name'] ?? null;
        $businessName = $draftData['business_name'] ?? null;
        $condition = $draftData['condition'] ?? null;
        $jobType = $draftData['job_type'] ?? null;
        $serviceArea = $draftData['service_area'] ?? null;
        $province = $draftData['province'] ?? null;
        $city = $draftData['city'] ?? null;
        $address = $draftData['address'] ?? null;
        $businessCategory = $draftData['business_category'] ?? null;
        $businessHours = $draftData['business_hours'] ?? null;

        if (request()->header('Accept-Language', 'en') === 'ar') {
            $title = helpers::translateText($title, 'ar');
            $description = helpers::translateText($description, 'ar');
            $companyName = helpers::translateText($companyName, 'ar');
            $businessName = helpers::translateText($businessName, 'ar');
            $jobType = helpers::translateText($jobType, 'ar');
            $serviceArea = helpers::translateText($serviceArea, 'ar');
            $province = helpers::translateText($province, 'ar');
            $city = helpers::translateText($city, 'ar');
            $address = helpers::translateText($address, 'ar');
            $businessCategory = helpers::translateText($businessCategory, 'ar');
            $businessHours = helpers::translateBusinessHours($businessHours, 'ar');

            if (is_array($condition)) {
                $condition = array_map(function ($item) {
                    return helpers::translateText($item, 'ar');
                }, $condition);
            } elseif (is_string($condition)) {
                $condition = helpers::translateText($condition, 'ar');
            }
        }

        if ($categorySlug === 'buy-sell') {
            return [
                'user_id' => $draftData['user_id'] ?? null,
                'user_name' => $user?->name,
                'user_since' => $user?->created_at ? (string) $user->created_at->format('Y') : null,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail' => isset($draftData['thumbnail']) ? $this->getImageUrl($draftData['thumbnail']) : null,
                'photos' => $resolvedPhotos,
                'title' => $title,
                'description' => $description,
                'price' => $draftData['price'] ?? null,
                'condition' => $condition,
                'province' => $province,
                'city' => $city,
                'address' => $address,
                'phone' => $draftData['phone'] ?? null,
                'whatsapp' => $draftData['whatsapp'] ?? null,
                'email' => $draftData['email'] ?? null,
                'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
                'status' => $draftData['status'] ?? null,
            ];
        } elseif ($categorySlug === 'jobs') {
            return [
                'user_id' => $draftData['user_id'] ?? null,
                'user_name' => $user?->name,
                'user_since' => $user?->created_at ? (string) $user->created_at->format('Y') : null,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail' => isset($draftData['thumbnail']) ? $this->getImageUrl($draftData['thumbnail']) : null,
                'title' => $title,
                'company_name' => $companyName,
                'description' => $description,
                'job_type' => $jobType,
                'province' => $province,
                'city' => $city,
                'address' => $address,
                'phone' => $draftData['phone'] ?? null,
                'whatsapp' => $draftData['whatsapp'] ?? null,
                'email' => $draftData['email'] ?? null,
                'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
                'status' => $draftData['status'] ?? null,
            ];
        } elseif ($categorySlug === 'business-directory') {
            return [
                'user_id' => $draftData['user_id'] ?? null,
                'user_name' => $user?->name,
                'user_since' => $user?->created_at ? (string) $user->created_at->format('Y') : null,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail' => isset($draftData['thumbnail']) ? $this->getImageUrl($draftData['thumbnail']) : null,
                'business_name' => $businessName,
                'business_category' => $businessCategory,
                'description' => $description,
                'website' => $draftData['website'] ?? null,
                'photos' => $resolvedPhotos,
                'province' => $province,
                'city' => $city,
                'address' => $address,
                'phone' => $draftData['phone'] ?? null,
                'whatsapp' => $draftData['whatsapp'] ?? null,
                'email' => $draftData['email'] ?? null,
                'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
                'logo' => isset($draftData['logo']) ? $this->getImageUrl($draftData['logo']) : null,
                'status' => $draftData['status'] ?? null,
                'business_hours' => $businessHours,
            ];
        } elseif ($categorySlug === 'services') {
            return [
                'user_id' => $draftData['user_id'] ?? null,
                'user_name' => $user?->name,
                'user_since' => $user?->created_at ? (string) $user->created_at->format('Y') : null,
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'category_slug' => $categorySlug,
                'thumbnail' => isset($draftData['thumbnail']) ? $this->getImageUrl($draftData['thumbnail']) : null,
                'photos' => $resolvedPhotos,
                'title' => $title,
                'description' => $description,
                'service_area' => $serviceArea,
                'province' => $province,
                'city' => $city,
                'address' => $address,
                'phone' => $draftData['phone'] ?? null,
                'whatsapp' => $draftData['whatsapp'] ?? null,
                'email' => $draftData['email'] ?? null,
                'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
                'status' => $draftData['status'] ?? null,
                'business_hours' => $businessHours,
            ];
        }

        return [
            'user_id' => $draftData['user_id'] ?? null,
            'user_name' => $user?->name,
            'user_since' => $user?->created_at ? (string) $user->created_at->format('Y') : null,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'category_slug' => $categorySlug,
            'thumbnail' => isset($draftData['thumbnail']) ? $this->getImageUrl($draftData['thumbnail']) : null,
            'title' => $title,
            'business_name' => $businessName,
            'company_name' => $companyName,
            'business_category' => $businessCategory,
            'description' => $description,
            'price' => $draftData['price'] ?? null,
            'condition' => $condition,
            'job_type' => $jobType,
            'service_area' => $serviceArea,
            'website' => $draftData['website'] ?? null,
            'logo' => isset($draftData['logo']) ? $this->getImageUrl($draftData['logo']) : null,
            'photos' => $resolvedPhotos,
            'province' => $province,
            'city' => $city,
            'address' => $address,
            'phone' => $draftData['phone'] ?? null,
            'whatsapp' => $draftData['whatsapp'] ?? null,
            'email' => $draftData['email'] ?? null,
            'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
            'status' => $draftData['status'] ?? null,
            'business_hours' => $businessHours,
        ];
    }

    public function update(UpdatePostRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!$user) {
                $message = translateHelper::translateCached('post.unauthenticated', 'Unauthenticated user.', $locale);
                return $this->sendError($message, [], 401);
            }

            $post = Post::where('id', $request->post_id)->where('user_id', $user->id)->first();
            if (!$post) {
                $message = translateHelper::translateCached('post.update.not_found', 'Post not found or unauthorized.', $locale);
                return $this->sendError($message, [], 404);
            }

            // Handle Category resolution
            $category = null;
            if ($request->has('category_slug') && !empty($request->category_slug)) {
                $category = Category::where('slug', $request->category_slug)->first();
            } elseif ($request->has('category_id') && !empty($request->category_id)) {
                $category = Category::find($request->category_id);
            }
            if ($category) {
                $post->category_id = $category->id;
            }

            // Upload files
            if ($request->hasFile('thumbnail')) {
                if ($post->thumbnail) {
                    helpers::deleteFile($post->thumbnail, config('app.file_system'));
                }
                $thumbnailName = time() . '_thumb_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
                $post->thumbnail = helpers::uploadFile('posts', $request->file('thumbnail'), $thumbnailName, config('app.file_system'));
            }

            if ($request->hasFile('logo')) {
                if ($post->logo) {
                    helpers::deleteFile($post->logo, config('app.file_system'));
                }
                $logoName = time() . '_logo_' . Str::random(10) . '.' . $request->file('logo')->getClientOriginalExtension();
                $post->logo = helpers::uploadFile('posts', $request->file('logo'), $logoName, config('app.file_system'));
            }

            if ($request->hasFile('photos')) {
                // Delete old photos
                if (is_array($post->photos)) {
                    foreach ($post->photos as $oldPhoto) {
                        helpers::deleteFile($oldPhoto, config('app.file_system'));
                    }
                }
                $uploadedPhotos = [];
                foreach ($request->file('photos') as $index => $file) {
                    $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                    $uploadedPhotos[] = $path;
                }
                $post->photos = $uploadedPhotos;
            }

            // Map standard fields if present
            if ($request->has('title'))
                $post->title = $request->title;
            if ($request->has('company_name'))
                $post->company_name = $request->company_name;
            if ($request->has('business_name'))
                $post->business_name = $request->business_name;
            if ($request->has('business_category'))
                $post->business_category = $request->business_category;
            if ($request->has('description'))
                $post->description = $request->description;
            if ($request->has('price'))
                $post->price = $request->price;
            if ($request->has('condition'))
                $post->condition = $request->condition;
            if ($request->has('job_type'))
                $post->job_type = $request->job_type;
            if ($request->has('service_area'))
                $post->service_area = $request->service_area;
            if ($request->has('province'))
                $post->province = $request->province;
            if ($request->has('city'))
                $post->city = $request->city;
            if ($request->has('address'))
                $post->address = $request->address;
            if ($request->has('phone'))
                $post->phone = $request->phone;
            if ($request->has('whatsapp'))
                $post->whatsapp = $request->whatsapp;
            if ($request->has('email'))
                $post->email = $request->email;
            if ($request->has('website'))
                $post->website = $request->website;
            if ($request->has('is_app_chat'))
                $post->is_app_chat = $request->boolean('is_app_chat');

            // If location changed, update geocoding coordinates
            if ($request->has('province') || $request->has('city') || $request->has('address')) {
                $postAddress = implode(', ', array_filter([$post->address, $post->city, $post->province]));
                $coords = $this->geoLocation->getCoordinates($postAddress);
                $post->latitude = $coords['latitude'] ?? null;
                $post->longitude = $coords['longitude'] ?? null;
            }

            // Force cleanup of unrelated fields depending on category
            $categorySlug = $post->category?->slug ?? '';
            if ($categorySlug === 'buy-and-sell' || $categorySlug === 'buy-sell') {
                $post->company_name = null;
                $post->business_name = null;
                $post->business_category = null;
                $post->job_type = null;
                $post->service_area = null;
                $post->website = null;
                if ($post->logo) {
                    helpers::deleteFile($post->logo, config('app.file_system'));
                    $post->logo = null;
                }
                if ($post->businessHours) {
                    $post->businessHours()->delete();
                }
            } elseif ($categorySlug === 'jobs') {
                $post->business_name = null;
                $post->business_category = null;
                $post->price = 0.00;
                $post->condition = null;
                $post->service_area = null;
                $post->website = null;
                if ($post->logo) {
                    helpers::deleteFile($post->logo, config('app.file_system'));
                    $post->logo = null;
                }
                if ($post->businessHours) {
                    $post->businessHours()->delete();
                }
            } elseif ($categorySlug === 'business-directory') {
                $post->title = null;
                $post->company_name = null;
                $post->price = 0.00;
                $post->condition = null;
                $post->job_type = null;
                $post->service_area = null;
            } elseif ($categorySlug === 'services') {
                $post->company_name = null;
                $post->business_name = null;
                $post->business_category = null;
                $post->price = 0.00;
                $post->condition = null;
                $post->job_type = null;
                $post->website = null;
                if ($post->logo) {
                    helpers::deleteFile($post->logo, config('app.file_system'));
                    $post->logo = null;
                }
                if ($post->businessHours) {
                    $post->businessHours()->delete();
                }
            }

            $dirty = $post->getDirty();
            if (!empty($dirty)) {
                $oldValues = [];
                $newValues = [];
                foreach ($dirty as $field => $newValue) {
                    if (in_array($field, ['created_at', 'updated_at', 'status'])) {
                        continue;
                    }
                    $oldValues[$field] = $post->getOriginal($field);
                    $newValues[$field] = $newValue;
                }

                if (!empty($newValues)) {
                    PostHistory::create([
                        'post_id' => $post->id,
                        'old_data' => $oldValues,
                        'new_data' => $newValues,
                    ]);
                }
            }

            $post->status = 'pending';
            $post->save();

            // Handle Business Hours
            if (($categorySlug === 'business-directory') && $request->has('business_hours')) {
                $businessHours = $request->business_hours;
                if (is_array($businessHours)) {
                    $normalizedHours = [];
                    foreach ($businessHours as $key => $value) {
                        $normalizedHours[strtolower(trim($key))] = $value;
                    }

                    $bh = $post->businessHours ?: new BusinessHour();
                    $bh->post_id = $post->id;
                    $bh->sunday = $normalizedHours['sunday'] ?? null;
                    $bh->monday = $normalizedHours['monday'] ?? null;
                    $bh->tuesday = $normalizedHours['tuesday'] ?? null;
                    $bh->wednesday = $normalizedHours['wednesday'] ?? null;
                    $bh->thursday = $normalizedHours['thursday'] ?? null;
                    $bh->friday = $normalizedHours['friday'] ?? null;
                    $bh->saturday = $normalizedHours['saturday'] ?? null;
                    $bh->save();
                }
            }

            $message = translateHelper::translateCached('post.update.success', 'Post updated successfully.', $locale);

            return $this->sendResponse(new PostResource($post->load(['category', 'businessHours'])), $message);
        } catch (Exception $e) {
            $message = translateHelper::translateCached('post.update.failed', 'Failed to update post.', $locale);
            return $this->sendError($message, ['error' => $e->getMessage()], 500);
        }
    }

    private function getImageUrl($path)
    {
        if (!$path) {
            return null;
        }
        try {
            return helpers::generateTempURL($path, config('app.file_system'));
        } catch (\Exception $e) {
            return \Illuminate\Support\Facades\Storage::disk(config('app.file_system'))->url($path);
        }
    }
}