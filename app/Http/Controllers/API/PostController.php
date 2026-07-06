<?php

namespace App\Http\Controllers\API;

use App\Enum\Role;
use App\Helpers\helpers;
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

            return $this->sendResponse(PostCardResource::collection($posts), 'Posts retrieved successfully.', 200, null, $posts);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve posts.', ['error' => $e->getMessage()], 500);
        }
    }

    public function show(ShowRequest $request)
    {
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
                return $this->sendError('Post not found.', [], 404);
            }

            $post->increment('views');

            return $this->sendResponse(new PostResource($post), 'Post retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve post.', ['error' => $e->getMessage()], 500);
        }
    }

    public function related(RelatedPostsRequest $request)
    {
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

            return $this->sendResponse(PostCardResource::collection($posts), 'Related posts retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve related posts.', ['error' => $e->getMessage()], 500);
        }
    }

    public function recent(IndexRequest $request)
    {
        try {
            $query = Post::where('status', 'active')->with('category');
            $this->applyFilters($query, $request);

            $posts = $query->orderBy('updated_at', 'desc')->take(10)->get();

            return $this->sendResponse(PostCardResource::collection($posts), 'Recent posts retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve posts.', ['error' => $e->getMessage()], 500);
        }
    }

    public function featured(IndexRequest $request)
    {
        try {
            $query = Post::where('status', 'active')->where('is_featured', true)->with('category');
            $this->applyFilters($query, $request);

            if (!$request->filled('sort_by')) {
                $query->latest();
            }

            $posts = $query->paginate($request->input('per_page', 10));

            return $this->sendResponse(PostCardResource::collection($posts), 'Posts retrieved successfully.', 200, null, $posts);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve posts.', ['error' => $e->getMessage()], 500);
        }
    }

    public function userPosts(AuthPostIndexRequest $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
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

            return $this->sendResponse([
                'total_post' => $totalPost,
                'total_view' => $totalView,
                'total_wish' => $totalWish,
                'total_message' => $totalMessage,
                'posts' => AuthPostResource::collection($posts),
            ], 'User posts retrieved successfully.', 200, null, $posts);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve user posts.', ['error' => $e->getMessage()], 500);
        }
    }

    public function deletePost(DeletePostRequest $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $post = Post::where('id', $request->post_id)->where('user_id', $user->id)->first();

            if (!$post) {
                return $this->sendError('Post not found or unauthorized.', [], 404);
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

            return $this->sendResponse([], 'Post deleted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to delete post.', ['error' => $e->getMessage()], 500);
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


    public function categories()
    {
        try {
            $categories = Category::where('status', 'active')->get();
            return $this->sendResponse(CategoriesResource::collection($categories), 'Categories retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve categories.', ['error' => $e->getMessage()], 500);
        }
    }

    public function saveDraft(SaveDraftRequest $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            if ($user->role === Role::GUEST) {
                return $this->sendError('Guest users cannot save drafts or create posts.', [], 403);
            }

            $existingDraftId = Cache::get('user_latest_draft_' . $user->id);

            if ($existingDraftId) {
                $draftKey = 'post_draft_' . $existingDraftId;
                $draftData = Cache::get($draftKey);

                if ($draftData) {
                    $uploadedPhotos = $draftData['photos'] ?? [];
                    if ($request->hasFile('photos')) {
                        $uploadedPhotos = [];
                        foreach ($request->file('photos') as $index => $file) {
                            $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                            $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                            $uploadedPhotos[] = $path;
                        }
                    }
                    $condition = $request->condition ? $request->condition : $draftData['condition'];

                    if ($request->has('category_slug')) {
                        $category = Category::where('slug', $request->category_slug)->first();
                        if ($category) {
                            $draftData['category_id'] = $category->id;
                            $draftData['category_name'] = $category->name;
                            $draftData['category_slug'] = $category->slug;
                        }
                    } elseif ($request->has('category_id')) {
                        $category = Category::find($request->category_id);
                        if ($category) {
                            $draftData['category_id'] = $category->id;
                            $draftData['category_name'] = $category->name;
                            $draftData['category_slug'] = $category->slug;
                        }
                    } elseif (!isset($draftData['category_name']) && isset($draftData['category_id'])) {
                        $category = Category::find($draftData['category_id']);
                        $draftData['category_name'] = $category?->name ?? '';
                        $draftData['category_slug'] = $category?->slug ?? '';
                    }

                    if ($request->hasFile('thumbnail')) {
                        $thumbnailName = time() . '_thumb_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
                        $draftData['thumbnail'] = helpers::uploadFile('posts', $request->file('thumbnail'), $thumbnailName, config('app.file_system'));
                    }
                    if (empty($draftData['thumbnail']) && !empty($uploadedPhotos)) {
                        $draftData['thumbnail'] = $uploadedPhotos[0];
                    }
                    if ($request->hasFile('logo')) {
                        $logoName = time() . '_logo_' . Str::random(10) . '.' . $request->file('logo')->getClientOriginalExtension();
                        $draftData['logo'] = helpers::uploadFile('posts', $request->file('logo'), $logoName, config('app.file_system'));
                    }

                    $draftData['title'] = $request->title ?? $draftData['title'];
                    $draftData['company_name'] = $request->company_name ?? ($draftData['company_name'] ?? null);
                    $draftData['business_name'] = $request->business_name ?? ($draftData['business_name'] ?? null);
                    $draftData['business_category'] = $request->business_category ?? ($draftData['business_category'] ?? null);
                    $draftData['description'] = $request->description ?? $draftData['description'];
                    $draftData['price'] = $request->price ?? $draftData['price'];
                    $draftData['condition'] = $condition;
                    $draftData['job_type'] = $request->has('job_type') ? $request->job_type : ($draftData['job_type'] ?? null);
                    $draftData['service_area'] = $request->has('service_area') ? $request->service_area : ($draftData['service_area'] ?? null);
                    $draftData['province'] = $request->province ?? $draftData['province'];
                    $draftData['city'] = $request->city ?? $draftData['city'];
                    $draftData['address'] = $request->address ?? $draftData['address'];
                    $draftData['phone'] = $request->phone ?? $draftData['phone'];
                    $draftData['whatsapp'] = $request->whatsapp ?? $draftData['whatsapp'];
                    $draftData['email'] = $request->email ?? $draftData['email'];
                    $draftData['website'] = $request->website ?? ($draftData['website'] ?? null);
                    $draftData['is_app_chat'] = $request->has('is_app_chat') ? $request->boolean('is_app_chat') : $draftData['is_app_chat'];
                    $draftData['photos'] = $uploadedPhotos;
                    if ($request->has('business_hours')) {
                        $draftData['business_hours'] = $request->business_hours;
                    }
                    if (!isset($draftData['created_at'])) {
                        $draftData['created_at'] = now()->toIso8601String();
                    }

                    $categorySlug = $draftData['category_slug'] ?? '';
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

                    return $this->sendResponse($this->resolvePhotosForResponse($draftData), 'Draft updated successfully.');
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

            return $this->sendResponse($this->resolvePhotosForResponse($draftData), 'Draft created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to save draft.', ['error' => $e->getMessage()], 500);
        }
    }

    public function getDraft()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $existingDraftId = Cache::get('user_latest_draft_' . $user->id);
            if (!$existingDraftId) {
                return $this->sendError('No draft found.', [], 404);
            }

            $draftData = Cache::get('post_draft_' . $existingDraftId);
            if (!$draftData) {
                return $this->sendError('Draft not found.', [], 404);
            }

            return $this->sendResponse($this->resolvePhotosForResponse($draftData), 'Draft retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve draft.', ['error' => $e->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            if ($user->role === Role::GUEST) {
                return $this->sendError('Guest users cannot create posts.', [], 403);
            }

            $draftId = Cache::get('user_latest_draft_' . $user->id);
            if (!$draftId) {
                return $this->sendError('No draft found to publish.', [], 404);
            }

            $draftKey = 'post_draft_' . $draftId;
            $draftData = Cache::get($draftKey);
            if (!$draftData) {
                return $this->sendError('Draft data not found.', [], 404);
            }

            if ($draftData['user_id'] !== $user->id) {
                return $this->sendError('Unauthorized.', [], 403);
            }

            $categoryId = $draftData['category_id'] ?? null;
            if (!$categoryId) {
                return $this->sendError('Missing required category_id in draft.', [], 422);
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

            return $this->sendResponse(new PostResource($post->load(['category', 'businessHours'])), 'Listing created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create listing.', ['error' => $e->getMessage()], 500);
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
                $timeAgo = Carbon::parse($createdAt)->diffForHumans();
            } catch (Exception $e) {
                // Ignore parsing errors
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
                'title' => $draftData['title'] ?? null,
                'description' => $draftData['description'] ?? null,
                'price' => $draftData['price'] ?? null,
                'condition' => $draftData['condition'] ?? null,
                'province' => $draftData['province'] ?? null,
                'city' => $draftData['city'] ?? null,
                'address' => $draftData['address'] ?? null,
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
                'title' => $draftData['title'] ?? null,
                'company_name' => $draftData['company_name'] ?? null,
                'description' => $draftData['description'] ?? null,
                'job_type' => $draftData['job_type'] ?? null,
                'province' => $draftData['province'] ?? null,
                'city' => $draftData['city'] ?? null,
                'address' => $draftData['address'] ?? null,
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
                'business_name' => $draftData['business_name'] ?? null,
                'business_category' => $draftData['business_category'] ?? null,
                'description' => $draftData['description'] ?? null,
                'website' => $draftData['website'] ?? null,
                'photos' => $resolvedPhotos,
                'province' => $draftData['province'] ?? null,
                'city' => $draftData['city'] ?? null,
                'address' => $draftData['address'] ?? null,
                'phone' => $draftData['phone'] ?? null,
                'whatsapp' => $draftData['whatsapp'] ?? null,
                'email' => $draftData['email'] ?? null,
                'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
                'logo' => isset($draftData['logo']) ? $this->getImageUrl($draftData['logo']) : null,
                'status' => $draftData['status'] ?? null,
                'business_hours' => $draftData['business_hours'] ?? null,
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
                'title' => $draftData['title'] ?? null,
                'description' => $draftData['description'] ?? null,
                'service_area' => $draftData['service_area'] ?? null,
                'province' => $draftData['province'] ?? null,
                'city' => $draftData['city'] ?? null,
                'address' => $draftData['address'] ?? null,
                'phone' => $draftData['phone'] ?? null,
                'whatsapp' => $draftData['whatsapp'] ?? null,
                'email' => $draftData['email'] ?? null,
                'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
                'status' => $draftData['status'] ?? null,
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
            'title' => $draftData['title'] ?? null,
            'business_name' => $draftData['business_name'] ?? null,
            'company_name' => $draftData['company_name'] ?? null,
            'business_category' => $draftData['business_category'] ?? null,
            'description' => $draftData['description'] ?? null,
            'price' => $draftData['price'] ?? null,
            'condition' => $draftData['condition'] ?? null,
            'job_type' => $draftData['job_type'] ?? null,
            'service_area' => $draftData['service_area'] ?? null,
            'website' => $draftData['website'] ?? null,
            'logo' => isset($draftData['logo']) ? $this->getImageUrl($draftData['logo']) : null,
            'photos' => $resolvedPhotos,
            'province' => $draftData['province'] ?? null,
            'city' => $draftData['city'] ?? null,
            'address' => $draftData['address'] ?? null,
            'phone' => $draftData['phone'] ?? null,
            'whatsapp' => $draftData['whatsapp'] ?? null,
            'email' => $draftData['email'] ?? null,
            'is_app_chat' => (bool) ($draftData['is_app_chat'] ?? false),
            'status' => $draftData['status'] ?? null,
        ];
    }

    public function update(UpdatePostRequest $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $post = Post::where('id', $request->post_id)->where('user_id', $user->id)->first();
            if (!$post) {
                return $this->sendError('Post not found or unauthorized.', [], 404);
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

            return $this->sendResponse(new PostResource($post->load(['category', 'businessHours'])), 'Post updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to update post.', ['error' => $e->getMessage()], 500);
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
