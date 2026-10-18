<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Mail\ListingApprovedMail;
use App\Mail\ListingRejectedMail;
use App\Models\Category;
use App\Models\Post;
use App\Models\Report;
use App\Models\SystemNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PostController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Post::with(['user', 'category'])->withCount('reports')->latest();

            if ($request->filled('status')) {
                $data->where('status', $request->status);
            }

            if ($request->filled('category_id')) {
                $data->where('category_id', $request->category_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('title', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('title->en', 'LIKE', "%{$keyword}%")
                          ->orWhere('title->ar', 'LIKE', "%{$keyword}%")
                          ->orWhere('business_name->en', 'LIKE', "%{$keyword}%")
                          ->orWhere('business_name->ar', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('user', function($query, $keyword) {
                    $query->whereHas('user', function($q) use ($keyword) {
                        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
                        $q->where(function($inner) use ($columns, $keyword) {
                            $first = true;
                            if (in_array('name', $columns)) {
                                $inner->where('name', 'LIKE', "%{$keyword}%");
                                $first = false;
                            }
                            if (in_array('first_name', $columns)) {
                                if ($first) { $inner->where('first_name', 'LIKE', "%{$keyword}%"); $first = false; }
                                else { $inner->orWhere('first_name', 'LIKE', "%{$keyword}%"); }
                            }
                            if (in_array('last_name', $columns)) {
                                if ($first) { $inner->where('last_name', 'LIKE', "%{$keyword}%"); $first = false; }
                                else { $inner->orWhere('last_name', 'LIKE', "%{$keyword}%"); }
                            }
                            if (in_array('email', $columns)) {
                                if ($first) { $inner->where('email', 'LIKE', "%{$keyword}%"); }
                                else { $inner->orWhere('email', 'LIKE', "%{$keyword}%"); }
                            }
                        });
                    });
                })
                ->setRowClass(function ($row) {
                    return $row->status === 'pending' ? 'bg-light-warning' : '';
                })
                ->addColumn('thumbnail', function ($row) {
                    $imgUrl = $row->thumbnail ? $this->getImageUrl($row->thumbnail) : asset('backend/assets/media/svg/files/blank-image.svg');
                    return '<img src="' . $imgUrl . '" class="w-50px h-50px object-fit-cover rounded" alt="thumb">';
                })
                ->addColumn('title', function ($row) {
                    $name = $row->title ?? $row->business_name ?? '-';
                    $rawCategory = $row->category->name ?? '-';
                    $category = __($rawCategory);
                    return '<div><span class="fw-bold">' . e($name) . '</span><br><small class="text-muted">' . e($category) . '</small></div>';
                })
                ->addColumn('user', function ($row) {
                    return $row->user->name ?? '-';
                })
                ->addColumn('price', function ($row) {
                    if (!$row->price || $row->price <= 0) return '-';
                    return '$' . number_format($row->price, 2);
                })
                ->addColumn('status', function ($row) {
                    $classes = [
                        'pending' => 'badge-light-warning',
                        'active'  => 'badge-light-success',
                        'rejected' => 'badge-light-danger',
                        'draft'   => 'badge-light-secondary',
                    ];
                    $statusKey = strtolower($row->status ?? '');
                    $class = $classes[$statusKey] ?? 'badge-light-secondary';
                    return '<span class="badge ' . $class . ' text-uppercase">' . __($statusKey) . '</span>';
                })
                ->addColumn('featured', function ($row) {
                    $checked = $row->is_featured ? 'checked' : '';
                    return '<div class="form-check form-switch">
                        <input class="form-check-input toggle-featured" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                    </div>';
                })
                ->addColumn('reports', function ($row) {
                    $count = $row->reports_count ?? 0;
                    if ($count > 0) {
                        $countText = app()->getLocale() == 'ar'
                            ? str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], $count)
                            : $count;
                        $label = app()->getLocale() == 'ar' ? 'بلاغ (' . $countText . ')' : $count . ' Report(s)';
                        return '<button class="btn btn-sm btn-light-danger view-post-reports-btn" data-id="' . $row->id . '" data-title="' . htmlspecialchars($row->title ?? $row->business_name ?? '') . '">' . $label . '</button>';
                    }
                    return '<span class="badge badge-light-secondary">' . __('No Reports') . '</span>';
                })
                ->addColumn('created_at', function ($row) {
                    return helpers::translateDate($row->created_at, 'h:i a, Y M d') ?: '-';
                })
                ->addColumn('actions', function ($row) {
                    return '
                        <div class="d-flex justify-content-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                               data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                ' . __('Actions') . '
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4"
                                 data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="' . route('post.show', $row->id) . '" class="menu-link px-3">' . __('View') . '</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 approve-post-btn" data-id="' . $row->id . '">' . __('Approve') . '</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 reject-post-btn" data-id="' . $row->id . '">' . __('Reject') . '</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 text-danger delete-post-btn" data-id="' . $row->id . '">' . __('Delete') . '</a>
                                </div>
                            </div>
                        </div>';
                })
                ->rawColumns(['thumbnail', 'title', 'status', 'featured', 'reports', 'actions'])
                ->make(true);
        }

        $categories = Category::all();
        return view('backend.layout.Post.index', compact('categories'));
    }

    public function show($id)
    {
        $post = Post::with(['user', 'category', 'businessHours', 'histories'])->findOrFail($id);
        $categories = Category::where('status', 'active')->get();

        // Mark matching notifications as read
        SystemNotification::where('is_read', false)
            ->where('target_url', route('post.show', $id))
            ->update(['is_read' => true]);

        return view('backend.layout.Post.show', compact('post', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'category_id'       => ['nullable', 'integer', 'exists:categories,id'],
            'title'             => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'price'             => ['nullable', 'numeric', 'min:0'],
            'status'            => ['nullable', 'in:pending,active,rejected'],
            'company_name'      => ['nullable', 'string', 'max:255'],
            'business_name'     => ['nullable', 'string', 'max:255'],
            'business_category' => ['nullable', 'string', 'max:255'],
            'website'           => ['nullable', 'string', 'max:255'],
            'thumbnail'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'logo'              => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'condition'         => ['nullable', 'array'],
            'job_type'          => ['nullable', 'array'],
            'service_area'      => ['nullable', 'array'],
            'photos'            => ['nullable', 'array'],
            'photos.*'          => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
        ]);

        try {
            $post->category_id       = $request->category_id       ?? $post->category_id;
            $post->title             = $request->title             ?? $post->title;
            $post->company_name      = $request->company_name      ?? $post->company_name;
            $post->business_name     = $request->business_name     ?? $post->business_name;
            $post->business_category = $request->business_category ?? $post->business_category;
            $post->description       = $request->description       ?? $post->description;
            $post->price             = $request->price             ?? $post->price;
            $post->province          = $request->province          ?? $post->province;
            $post->city              = $request->city              ?? $post->city;
            $post->address           = $request->address           ?? $post->address;
            $post->phone             = $request->phone             ?? $post->phone;
            $post->whatsapp          = $request->whatsapp          ?? $post->whatsapp;
            $post->email             = $request->email             ?? $post->email;
            $post->website           = $request->website           ?? $post->website;
            $post->condition         = $request->condition;
            $post->job_type          = $request->job_type;
            $post->service_area      = $request->service_area;

            // Handle thumbnail upload/removal
            if ($request->hasFile('thumbnail')) {
                if ($post->thumbnail) {
                    helpers::deleteFile($post->thumbnail, config('app.file_system'));
                }
                $thumbnailName = time() . '_thumb_' . Str::random(10) . '.' . $request->file('thumbnail')->getClientOriginalExtension();
                $post->thumbnail = helpers::uploadFile('posts', $request->file('thumbnail'), $thumbnailName, config('app.file_system'));
            } else if ($request->input('thumbnail_remove')) {
                if ($post->thumbnail) {
                    helpers::deleteFile($post->thumbnail, config('app.file_system'));
                }
                $post->thumbnail = null;
            }

            // Handle logo upload/removal
            if ($request->hasFile('logo')) {
                if ($post->logo) {
                    helpers::deleteFile($post->logo, config('app.file_system'));
                }
                $logoName = time() . '_logo_' . Str::random(10) . '.' . $request->file('logo')->getClientOriginalExtension();
                $post->logo = helpers::uploadFile('posts', $request->file('logo'), $logoName, config('app.file_system'));
            } else if ($request->input('logo_remove')) {
                if ($post->logo) {
                    helpers::deleteFile($post->logo, config('app.file_system'));
                }
                $post->logo = null;
            }

            // Handle photos update (per-index, dynamic limit)
            $newPhotos = [];
            $existingPhotos = $request->input('existing_photos', []);
            $photosRemove = $request->input('photos_remove', []);
            
            $indices = array_unique(array_merge(
                array_keys($request->file('photos') ?? []),
                array_keys($existingPhotos),
                array_keys($photosRemove)
            ));
            sort($indices);
            
            foreach ($indices as $i) {
                if ($request->hasFile("photos.$i")) {
                    $file = $request->file("photos.$i");
                    $fileName = time() . '_photo_' . Str::random(10) . '_' . $i . '.' . $file->getClientOriginalExtension();
                    $path = helpers::uploadFile('posts', $file, $fileName, config('app.file_system'));
                    $newPhotos[] = $path;
                    
                    if (isset($existingPhotos[$i]) && $existingPhotos[$i]) {
                        helpers::deleteFile($existingPhotos[$i], config('app.file_system'));
                    }
                } else if ($request->input("photos_remove.$i") == '1') {
                    if (isset($existingPhotos[$i]) && $existingPhotos[$i]) {
                        helpers::deleteFile($existingPhotos[$i], config('app.file_system'));
                    }
                } else if (isset($existingPhotos[$i]) && $existingPhotos[$i]) {
                    $newPhotos[] = $existingPhotos[$i];
                }
            }
            
            if (is_array($post->photos)) {
                foreach ($post->photos as $oldPath) {
                    if (!in_array($oldPath, $newPhotos)) {
                        helpers::deleteFile($oldPath, config('app.file_system'));
                    }
                }
            }
            $post->photos = !empty($newPhotos) ? $newPhotos : null;

            $post->save();

            return response()->json(['success' => true, 'message' => 'Post updated successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        try {
            $post = Post::with(['user', 'category'])->findOrFail($id);
            $post->status = 'active';
            $post->save();

            $creator = $post->user;
            $locale = $creator?->locale;
            if (empty($locale)) {
                $rawTitle = (string) ($post->getRawOriginal('title') ?? $post->title ?? '');
                $locale = preg_match('/\p{Arabic}/u', $rawTitle) ? 'ar' : 'en';
            }

            // Resolve listing title
            $postTitle = '';
            if (method_exists($post, 'getTranslation')) {
                $postTitle = $post->getTranslation('title', $locale, false);
            }
            if (empty($postTitle)) {
                $postTitle = $post->title ?: ($post->business_name ?: ($locale === 'ar' ? 'الإعلان' : 'Listing'));
            }

            // Notification title and message (matching design: "Listing Approved" / "Your listing ‘...’ has been approved and is now live.")
            if ($locale === 'ar') {
                $notificationTitle = 'تمت الموافقة على الإعلان';
                $notificationMessage = "تمت الموافقة على إعلانك ‘{$postTitle}’ وهو متاح الآن.";
            } else {
                $notificationTitle = 'Listing Approved';
                $notificationMessage = "Your listing ‘{$postTitle}’ has been approved and is now live.";
            }

            // 1. Create In-App Notification for Creator
            if ($creator) {
                \App\Models\UserNotification::create([
                    'user_id' => $creator->id,
                    'type'    => 'listing_approved',
                    'title'   => $notificationTitle,
                    'message' => $notificationMessage,
                    'data'    => [
                        'post_id' => $post->id,
                    ],
                    'is_read' => false,
                ]);
            }

            // 2. Send Firebase Push Notification to creator's mobile devices
            if ($creator && $creator->enable_notification) {
                try {
                    $tokens = $creator->firebaseTokens()
                        ->where('is_active', true)
                        ->pluck('token')
                        ->filter()
                        ->unique()
                        ->toArray();

                    if (!empty($tokens)) {
                        $pushData = [
                            'title' => $notificationTitle,
                            'message' => $notificationMessage,
                            'data' => [
                                'type' => 'listing_approved',
                                'post_id' => (string) $post->id,
                            ],
                        ];

                        foreach ($tokens as $token) {
                            helpers::sendNotifyMobile($token, $pushData);
                        }
                    }
                } catch (\Exception $pushEx) {
                    Log::error('Firebase push notification failed for post approval: ' . $pushEx->getMessage());
                }
            }

            // 2. Send Email Notification to post's email and creator's email (if email notification enabled)
            $recipientEmails = [];
            if ($post->email) {
                $recipientEmails[] = $post->email;
            }
            if ($creator?->email && $creator->enable_notification && $creator->email_notification) {
                $recipientEmails[] = $creator->email;
            }
            $recipientEmails = array_unique(array_filter($recipientEmails));

            if (!empty($recipientEmails)) {
                try {
                    $recipientName = $creator?->name ?? ($locale === 'ar' ? 'المستخدم' : 'User');
                    foreach ($recipientEmails as $email) {
                        Mail::to($email)->send(new ListingApprovedMail(
                            $post,
                            $postTitle,
                            $notificationTitle,
                            $notificationMessage,
                            $recipientName,
                            $locale
                        ));
                    }
                } catch (\Exception $mailEx) {
                    Log::error('Email notification failed for post approval: ' . $mailEx->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Post approved successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $post = Post::with(['user', 'category'])->findOrFail($id);
            $post->status = 'rejected';
            $post->save();

            $creator = $post->user;
            $locale = $creator?->locale;
            if (empty($locale)) {
                $rawTitle = (string) ($post->getRawOriginal('title') ?? $post->title ?? '');
                $locale = preg_match('/\p{Arabic}/u', $rawTitle) ? 'ar' : 'en';
            }

            // Resolve listing title
            $postTitle = '';
            if (method_exists($post, 'getTranslation')) {
                $postTitle = $post->getTranslation('title', $locale, false);
            }
            if (empty($postTitle)) {
                $postTitle = $post->title ?: ($post->business_name ?: ($locale === 'ar' ? 'الإعلان' : 'Listing'));
            }

            $reason = trim((string) $request->input('reason', ''));

            if ($locale === 'ar') {
                $notificationTitle = 'تم رفض الإعلان';
                $notificationMessage = $reason
                    ? "تم رفض إعلانك ‘{$postTitle}’. السبب: {$reason}"
                    : "نأسف، لم تتم الموافقة على إعلانك ‘{$postTitle}’.";
            } else {
                $notificationTitle = 'Listing Rejected';
                $notificationMessage = $reason
                    ? "Your listing ‘{$postTitle}’ was not approved. Reason: {$reason}"
                    : "We're sorry, your listing ‘{$postTitle}’ was not approved.";
            }

            // 1. Create In-App Notification for Creator
            if ($creator) {
                \App\Models\UserNotification::create([
                    'user_id' => $creator->id,
                    'type'    => 'listing_rejected',
                    'title'   => $notificationTitle,
                    'message' => $notificationMessage,
                    'data'    => [
                        'post_id' => $post->id,
                        'reason'  => $reason ?: null,
                    ],
                    'is_read' => false,
                ]);
            }

            // 2. Send Firebase Push Notification to creator's mobile devices
            if ($creator && $creator->enable_notification) {
                try {
                    $tokens = $creator->firebaseTokens()
                        ->where('is_active', true)
                        ->pluck('token')
                        ->filter()
                        ->unique()
                        ->toArray();

                    if (!empty($tokens)) {
                        $pushData = [
                            'title' => $notificationTitle,
                            'message' => $notificationMessage,
                            'channel_id' => 'high_importance_channel',
                            'data' => [
                                'type'    => 'listing_rejected',
                                'post_id' => (string) $post->id,
                                'reason'  => (string) $reason,
                            ],
                        ];

                        foreach ($tokens as $token) {
                            helpers::sendNotifyMobile($token, $pushData);
                        }
                    }
                } catch (\Exception $pushEx) {
                    Log::error('Firebase push notification failed for post rejection: ' . $pushEx->getMessage());
                }
            }

            // 3. Send Email Notification to post's email and creator's email (if email notification enabled)
            $recipientEmails = [];
            if ($post->email) {
                $recipientEmails[] = $post->email;
            }
            if ($creator?->email && $creator->enable_notification && $creator->email_notification) {
                $recipientEmails[] = $creator->email;
            }
            $recipientEmails = array_unique(array_filter($recipientEmails));

            if (!empty($recipientEmails)) {
                try {
                    $recipientName = $creator?->name ?? ($locale === 'ar' ? 'المستخدم' : 'User');
                    foreach ($recipientEmails as $email) {
                        Mail::to($email)->send(new ListingRejectedMail(
                            $post,
                            $postTitle,
                            $notificationTitle,
                            $notificationMessage,
                            $recipientName,
                            $locale,
                            $reason ?: null
                        ));
                    }
                } catch (\Exception $mailEx) {
                    Log::error('Email notification failed for post rejection: ' . $mailEx->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Post rejected successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleFeatured(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->is_featured = $request->boolean('featured');
            $post->save();
            return response()->json(['success' => true, 'message' => 'Featured status updated.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($post->thumbnail) helpers::deleteFile($post->thumbnail, config('app.file_system'));
            if ($post->logo) helpers::deleteFile($post->logo, config('app.file_system'));
            if (is_array($post->photos)) {
                foreach ($post->photos as $photo) {
                    helpers::deleteFile($photo, config('app.file_system'));
                }
            }
            $post->delete();
            return response()->json(['success' => true, 'message' => 'Post deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getImageUrl($path)
    {
        if (!$path) return null;
        try {
            return helpers::generateTempURL($path, config('app.file_system'));
        } catch (Exception $e) {
            return \Illuminate\Support\Facades\Storage::disk(config('app.file_system'))->url($path);
        }
    }

    /**
     * Get reports against a specific post
     */
    public function getPostReports($id)
    {
        try {
            $post = Post::findOrFail($id);
            $reports = Report::where('post_id', $id)->with('user')->latest()->get();

            // Translate reason and other_note for AJAX response
            $locale = app()->getLocale();
            $reportsData = $reports->map(function ($report) use ($locale) {
                $data = $report->toArray();
                $data['reason'] = $report->getTranslation('reason', $locale);
                $data['other_note'] = $report->getTranslation('other_note', $locale);
                $data['user'] = $report->user;
                return $data;
            });

            return response()->json([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title ?? $post->business_name ?? 'Untitled Listing',
                    'status' => $post->status,
                ],
                'reports' => $reportsData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all reports against a specific post as read
     */
    public function markAllPostReportsRead($id)
    {
        try {
            Report::where('post_id', $id)->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'All post reports marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
