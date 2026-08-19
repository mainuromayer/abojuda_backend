<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display list of role
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Group by type
            $data = Role::with('permissions')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('permissions', function ($data) {
                    $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];

                    $badges = $data->permissions->map(function ($permission) use ($colors) {
                        $randomColor = $colors[array_rand($colors)];
                        return '<span class="badge badge-' . $randomColor . ' badge">' . e($permission->name) . '</span>';
                    });

                    return $badges->implode(' ');
                })
                ->addColumn('actions', function ($row) {
                    return '
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                                   data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    ' . __('Actions') . '
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                     data-kt-menu="true">
                                     <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 edit-role-btn" data-id="' . $row->id . '">' . __('Edit') . '</a>
                                    </div>
                                     <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-role-btn" data-id="' . $row->id . '">' . __('Delete') . '</a>
                                    </div>
                                </div>
                            </div>

                            ';
                })
                ->rawColumns(['permissions', 'actions'])
                ->make(true);
        }
        $permissions = Permission::orderBy('name')->pluck('name', 'id')->toArray();
        return view('backend.layout.Role.index', compact('permissions'));
    }


    /**
     * store new role with permission
     */
    public function store(Request $request)
    {
        $validation = $request->validate([
            'role'          => ['required', 'min:4', 'unique:roles,name'],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'exists:permissions,id'],
        ]);

        try {
            // Fetch permission names based on the permission IDs provided in the request
            $permissionIds = $request->permissions;
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            //create role
            $role = Role::create(['name' => $request->role]);

            // Assign each permission to the role
            foreach ($permissions as $permission) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
            return response()->json(['success' => true, 'message' => 'Role created successfully']);
        }catch (\Exception $exception){
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Edit role with role id
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $allPermissions = Permission::all();

        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ],
            'permissions' => [
                'all' => $allPermissions
            ]
        ]);
    }


    /**
     * Update role
     */
    public function update(Request $request, $id)
    {
        $validation = $request->validate(['role' => ['required', 'min:4', 'unique:roles,name,' . $id]]);

        try {
            $role = Role::findById($id);
            $role->name = $request->role;
            $role->save();


            // Remove all existing permissions before assigning new ones
            $role->permissions()->detach();

            // Fetch permission names based on the permission IDs provided in the request
            $permissionIds = $request->permissions;
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            // Assign each permission to the role
            foreach ($permissions as $permission) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }

            return response()->json(['success' => true, 'message' => 'Role updated successfully']);
        }catch (\Exception $exception){
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::findById($id);
            // Remove all existing permissions before assigning new ones
            $role->permissions()->detach();
            $role->delete();
            return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
        }catch (\Exception $exception){
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }
}
