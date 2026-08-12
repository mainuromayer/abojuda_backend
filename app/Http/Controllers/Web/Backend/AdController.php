<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdController extends Controller
{
    /**
     * Display a listing of ads
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Ad::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('media', function ($row) {
                    if (!$row->media) {
                        $defaultImg = asset('backend/assets/media/svg/files/blank-image.svg');
                        return '<img src="' . $defaultImg . '" class="w-60px h-40px object-fit-cover rounded" alt="media">';
                    }
                    $mediaUrl = $this->getMediaUrl($row->media);
                    if ($row->media_type === 'video') {
                        return '<video src="' . $mediaUrl . '" class="w-60px h-40px object-fit-cover rounded" controls></video>';
                    }
                    return '<img src="' . $mediaUrl . '" class="w-60px h-40px object-fit-cover rounded" alt="media">';
                })
                ->addColumn('title', function ($row) {
                    return e($row->title);
                })
                ->addColumn('media_type', function ($row) {
                    $badgeClass = $row->media_type === 'video' ? 'badge-light-warning' : 'badge-light-info';
                    return '<span class="badge ' . $badgeClass . ' text-uppercase">' . e($row->media_type) . '</span>';
                })
                ->addColumn('back_link', function ($row) {
                    if (!$row->back_link) {
                        return '-';
                    }
                    return '<a href="' . e($row->back_link) . '" target="_blank" class="text-primary text-hover-underline text-truncate d-inline-block mw-200px">' . e($row->back_link) . '</a>';
                })
                ->addColumn('status', function ($row) {
                    $badgeClass = $row->status === 'active' ? 'badge-light-success' : 'badge-light-danger';
                    return '<span class="badge ' . $badgeClass . ' text-uppercase">' . e($row->status) . '</span>';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d M Y, h:i a') : '-';
                })
                ->addColumn('actions', function ($row) {
                    return '
                        <div class="d-flex justify-content-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                               data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                 data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 edit-ad-btn" data-id="' . $row->id . '">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 delete-ad-btn" data-id="' . $row->id . '">Delete</a>
                                </div>
                            </div>
                        </div>';
                })
                ->rawColumns(['media', 'media_type', 'back_link', 'status', 'actions'])
                ->make(true);
        }

        return view('backend.layout.Ad.index');
    }

    /**
     * Store a newly created ad in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'media_type' => ['required', 'string', 'in:image,video'],
            'media_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,svg,gif,webp', 'max:10240'],
            'media_video' => ['nullable', 'file', 'mimes:mp4,mov,avi,mkv', 'max:30720'],
            'back_link' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        try {
            $mediaPath = null;
            $mediaType = $validated['media_type'];

            $file = $mediaType === 'video' ? $request->file('media_video') : $request->file('media_image');
            if ($file) {
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $mediaPath = helpers::uploadFile('ads', $file, $fileName, config('app.file_system'));
            }

            Ad::create([
                'title' => $validated['title'],
                'media' => $mediaPath,
                'media_type' => $mediaType,
                'back_link' => $validated['back_link'] ?? null,
                'status' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad created successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified ad
     */
    public function edit($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->media_url = $ad->media ? $this->getMediaUrl($ad->media) : null;
        return response()->json(['ad' => $ad]);
    }

    /**
     * Update the specified ad in storage
     */
    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'media_type' => ['required', 'string', 'in:image,video'],
            'media_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,svg,gif,webp', 'max:10240'],
            'media_video' => ['nullable', 'file', 'mimes:mp4,mov,avi,mkv', 'max:30720'],
            'back_link' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'media_remove' => ['nullable', 'boolean'],
        ]);

        try {
            $mediaType = $validated['media_type'];
            $ad->media_type = $mediaType;

            $file = $mediaType === 'video' ? $request->file('media_video') : $request->file('media_image');

            if ($file) {
                if ($ad->media) {
                    helpers::deleteFile($ad->media, config('app.file_system'));
                }
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $ad->media = helpers::uploadFile('ads', $file, $fileName, config('app.file_system'));
            } elseif ($request->input('media_remove')) {
                if ($ad->media) {
                    helpers::deleteFile($ad->media, config('app.file_system'));
                }
                $ad->media = null;
            }

            $ad->update([
                'title' => $validated['title'],
                'media' => $ad->media,
                'media_type' => $ad->media_type,
                'back_link' => $validated['back_link'] ?? null,
                'status' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad updated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified ad from storage
     */
    public function destroy($id)
    {
        try {
            $ad = Ad::findOrFail($id);
            if ($ad->media) {
                helpers::deleteFile($ad->media, config('app.file_system'));
            }
            $ad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ad deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to retrieve URL for local or S3 driver
     */
    private function getMediaUrl($path)
    {
        if (!$path) {
            return null;
        }
        try {
            $url = helpers::generateTempURL($path, config('app.file_system'));
            if ($url) {
                return $url;
            }
            return asset('storage/' . $path);
        } catch (Exception $e) {
            return asset('storage/' . $path);
        }
    }
}
