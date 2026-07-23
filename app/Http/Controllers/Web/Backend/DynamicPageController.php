<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\DynamicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class DynamicPageController extends Controller
{
    /**
     * Display list of dynamic pages
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DynamicPage::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($row) {
                    return $row->title;
                })
                ->addColumn('slug', function ($row) {
                    $slug = $row->slug ?? '-';
                    return '
                    <div class="d-flex align-items-center">
                        <span class="me-2">' . htmlspecialchars($slug) . '</span>
                        <button class="btn btn-icon btn-sm btn-light copy-btn" data-slug="' . htmlspecialchars($slug) . '">
                            <i class="ki-duotone ki-copy fs-4"></i>
                        </button>
                    </div>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('dynamic-pages.edit',['id' => $row->id]);
                    $showUrl = route('dynamic-pages.show', ['id' => $row->id]);
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
                                    <a href="' . $showUrl . '" class="menu-link px-3 view-page-btn">View</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="' . $editUrl . '" class="menu-link px-3 edit-page-btn">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 delete-page-btn" data-id="' . $row->id . '">Delete</a>
                                </div>
                            </div>
                        </div>';
                })
                ->rawColumns(['slug','actions'])
                ->make(true);
        }
        return view('backend.layout.dynamic_pages.index');
    }

    /**
     * get create dynamic page
     */
    public function create()
    {
        return view('backend.layout.dynamic_pages.create');
    }

    /**
     * Store a new dynamic page and notify all users
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:dynamic_pages,title',
            'text' => 'required|string',
        ]);

        try {
            $page = DynamicPage::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->text,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Page created successfully.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DynamicPage creation failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create page: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a dynamic page details
     */
    public function show($id)
    {
        $page = DynamicPage::find($id);
        if (!$page)
        {
            return redirect()->route('dynamic-pages.index')->with('error', 'Page not found');
        }
        return view('backend.layout.dynamic_pages.show', compact('page'));
    }

    /**
     * Show edit form for a dynamic page
     */
    public function edit($id)
    {
        $page = DynamicPage::find($id);
        if (!$page)
        {
            return redirect()->route('dynamic-pages.index')->with('error', 'Page not found');
        }
        return view('backend.layout.dynamic_pages.edit', compact('page'));
    }

    /**
     * Update a dynamic page
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:dynamic_pages,title,' . $id,
            'text' => 'required|string',
        ]);

        try {
            $page = DynamicPage::findOrFail($id);
            $page->update([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->text,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DynamicPage update failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update page: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a dynamic page
     */
    public function destroy($id)
    {
        try {
            $page = DynamicPage::findOrFail($id);
            $page->delete();

            return response()->json([
                'success' => true,
                'message' => 'Page deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete page.',
            ], 500);
        }
    }
}
