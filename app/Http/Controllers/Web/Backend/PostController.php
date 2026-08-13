<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Report;
use App\Models\SystemNotification;
use Exception;
use Illuminate\Http\Request;
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
                    $formattedPrice = '$' . number_format($row->price, 2);
                    if (app()->getLocale() === 'ar') {
                        return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], $formattedPrice);
                    }
                    return $formattedPrice;
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
                    if (!$row->created_at) return '-';
                    $dateStr = $row->created_at->format('d M Y, h:i a');
                    if (app()->getLocale() === 'ar') {
                        return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], $dateStr);
                    }
                    return $dateStr;
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
            $post = Post::findOrFail($id);
            $post->status = 'active';
            $post->save();
            return response()->json(['success' => true, 'message' => 'Post approved successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $post = Post::findOrFail($id);
            $post->status = 'rejected';
            $post->save();
            return response()->json(['success' => true, 'message' => 'Post rejected.']);
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

            return response()->json([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title ?? $post->business_name ?? 'Untitled Listing',
                    'status' => $post->status,
                ],
                'reports' => $reports
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
