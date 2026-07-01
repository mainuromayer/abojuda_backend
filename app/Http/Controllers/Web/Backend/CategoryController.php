<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('icon', function ($row) {
                    $iconUrl = $row->icon ? $this->getImageUrl($row->icon) : asset('backend/assets/media/svg/files/blank-image.svg');
                    return '<img src="' . $iconUrl . '" class="w-40px h-40px object-fit-cover rounded" alt="icon">';
                })
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('description', function ($row) {
                    return $row->shot_desc ?? '-';
                })
                ->addColumn('status', function ($row) {
                    $badgeClass = $row->status === 'active' ? 'badge-light-success' : 'badge-light-danger';
                    return '<span class="badge ' . $badgeClass . ' text-uppercase">' . $row->status . '</span>';
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
                                    <a href="#" class="menu-link px-3 edit-category-btn" data-id="' . $row->id . '">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 delete-category-btn" data-id="' . $row->id . '">Delete</a>
                                </div>
                            </div>
                        </div>';
                })
                ->rawColumns(['icon', 'status', 'actions'])
                ->make(true);
        }

        return view('backend.layout.Category.index');
    }

    /**
     * Store a newly created category in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'shot_desc' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'icon' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'],
        ]);

        try {
            $iconPath = null;
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $iconPath = helpers::uploadFile('categories', $file, $fileName, config('app.file_system'));
            }

            Category::create([
                'name' => $validated['name'],
                'shot_desc' => $validated['shot_desc'] ?? null,
                'status' => $validated['status'],
                'icon' => $iconPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $category->icon_url = $category->icon ? $this->getImageUrl($category->icon) : null;
        return response()->json(['category' => $category]);
    }

    /**
     * Update the specified category in storage
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $id],
            'shot_desc' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'icon' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'],
            'icon_remove' => ['nullable', 'boolean'],
        ]);

        try {
            // Handle icon file upload/removal
            if ($request->hasFile('icon')) {
                if ($category->icon) {
                    helpers::deleteFile($category->icon, config('app.file_system'));
                }
                $file = $request->file('icon');
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $category->icon = helpers::uploadFile('categories', $file, $fileName, config('app.file_system'));
            } elseif ($request->input('icon_remove')) {
                if ($category->icon) {
                    helpers::deleteFile($category->icon, config('app.file_system'));
                }
                $category->icon = null;
            }

            $category->update([
                'name' => $validated['name'],
                'shot_desc' => $validated['shot_desc'] ?? null,
                'status' => $validated['status'],
                'icon' => $category->icon,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            if ($category->icon) {
                helpers::deleteFile($category->icon, config('app.file_system'));
            }
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to retrieve URL for local or S3 driver safely without throwing driver-support exception
     */
    private function getImageUrl($path)
    {
        if (!$path) {
            return null;
        }
        try {
            return helpers::generateTempURL($path, config('app.file_system'));
        } catch (Exception $e) {
            return Storage::disk(config('app.file_system'))->url($path);
        }
    }
}
