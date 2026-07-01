<?php

namespace App\Http\Controllers\API;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Category\CategoriesResource;
use App\Http\Resources\Api\V1\Post\PostResource;
use App\Http\Requests\Api\V1\Post\SaveDraftRequest;
use App\Http\Requests\Api\V1\Post\CreateRequest;
use App\Models\Category;
use App\Models\Post;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    use ApiResponse;

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

            if ($request->filled('draft_id')) {
                $draftKey = 'post_draft_' . $request->draft_id;
                $draftData = Cache::get($draftKey);

                if (!$draftData) {
                    return $this->sendError('Draft not found.', [], 404);
                }

                if ($draftData['user_id'] !== $user->id) {
                    return $this->sendError('Unauthorized.', [], 403);
                }

                $uploadedPhotos = $draftData['photos'] ?? [];
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $index => $file) {
                        $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                        $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                        $uploadedPhotos[] = $path;
                    }
                }

                $condition = $request->condition ? $request->condition : $draftData['condition'];

                $draftData['title'] = $request->title ?? $draftData['title'];
                $draftData['description'] = $request->description ?? $draftData['description'];
                $draftData['price'] = $request->price ?? $draftData['price'];
                $draftData['condition'] = $condition;
                $draftData['province'] = $request->province ?? $draftData['province'];
                $draftData['city'] = $request->city ?? $draftData['city'];
                $draftData['address'] = $request->address ?? $draftData['address'];
                $draftData['phone'] = $request->phone ?? $draftData['phone'];
                $draftData['whatsapp'] = $request->whatsapp ?? $draftData['whatsapp'];
                $draftData['email'] = $request->email ?? $draftData['email'];
                $draftData['is_app_chat'] = $request->has('is_app_chat') ? $request->boolean('is_app_chat') : $draftData['is_app_chat'];
                $draftData['photos'] = $uploadedPhotos;
                $draftData['thumbnail'] = !empty($uploadedPhotos) ? $uploadedPhotos[0] : null;

                Cache::put($draftKey, $draftData, now()->addDays(7));

                return $this->sendResponse(array_merge(['draft_id' => $request->draft_id], $draftData), 'Draft updated successfully.');
            }

            $uploadedPhotos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $file) {
                    $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                    $uploadedPhotos[] = $path;
                }
            }

            $draftId = (int) Cache::get('post_draft_counter', 0) + 1;
            Cache::forever('post_draft_counter', $draftId);

            $condition = $request->condition ? $request->condition : null;

            $draftData = [
                'user_id' => $user->id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price ?? 0.00,
                'condition' => $condition,
                'province' => $request->province,
                'city' => $request->city,
                'address' => $request->address,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'email' => $request->email,
                'is_app_chat' => $request->boolean('is_app_chat'),
                'photos' => $uploadedPhotos,
                'thumbnail' => !empty($uploadedPhotos) ? $uploadedPhotos[0] : null,
                'status' => 'draft',
            ];

            Cache::put('post_draft_' . $draftId, $draftData, now()->addDays(7));
            Cache::put('user_latest_draft_' . $user->id, $draftId, now()->addDays(7));

            return $this->sendResponse(array_merge(['draft_id' => $draftId], $draftData), 'Draft created successfully.');
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

            $draftId = Cache::get('user_latest_draft_' . $user->id);
            if (!$draftId) {
                return $this->sendError('No draft found.', [], 404);
            }

            $draftData = Cache::get('post_draft_' . $draftId);
            if (!$draftData) {
                return $this->sendError('Draft not found.', [], 404);
            }

            return $this->sendResponse(array_merge(['draft_id' => (int) $draftId], $draftData), 'Draft retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve draft.', ['error' => $e->getMessage()], 500);
        }
    }

    public function create(CreateRequest $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $draftKey = 'post_draft_' . $request->draft_id;
            $draftData = Cache::get($draftKey);
            if (!$draftData) {
                return $this->sendError('Draft not found.', [], 404);
            }

            if ($draftData['user_id'] !== $user->id) {
                return $this->sendError('Unauthorized.', [], 403);
            }

            $uploadedPhotos = $draftData['photos'] ?? [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $file) {
                    $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                    $uploadedPhotos[] = $path;
                }
            }

            $categoryId = $request->category_id ?? $draftData['category_id'];
            $title = $request->title ?? $draftData['title'];
            $description = $request->description ?? $draftData['description'];
            $price = $request->price ?? $draftData['price'];
            $condition = $request->condition ? $request->condition : $draftData['condition'];
            $province = $request->province ?? $draftData['province'];
            $city = $request->city ?? $draftData['city'];
            $address = $request->address ?? $draftData['address'];
            $phone = $request->phone ?? $draftData['phone'];
            $whatsapp = $request->whatsapp ?? $draftData['whatsapp'];
            $email = $request->email ?? $draftData['email'];
            $isAppChat = $request->has('is_app_chat') ? $request->boolean('is_app_chat') : $draftData['is_app_chat'];

            if (!$categoryId || !$title || !$description || $price === null || !$condition || !$province || !$city || empty($uploadedPhotos)) {
                return $this->sendError('Missing required fields for listing publication.', [], 422);
            }

            $post = new Post();
            $post->user_id = $user->id;
            $post->category_id = $categoryId;
            $post->title = $title;
            $post->description = $description;
            $post->price = $price;
            $post->condition = $condition;
            $post->province = $province;
            $post->city = $city;
            $post->address = $address;
            $post->phone = $phone;
            $post->whatsapp = $whatsapp;
            $post->email = $email;
            $post->is_app_chat = $isAppChat;
            $post->photos = $uploadedPhotos;
            $post->thumbnail = !empty($uploadedPhotos) ? $uploadedPhotos[0] : null;
            $post->status = 'pending';
            $post->save();

            Cache::forget($draftKey);

            return $this->sendResponse(new PostResource($post), 'Listing created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create listing.', ['error' => $e->getMessage()], 500);
        }
    }
}
