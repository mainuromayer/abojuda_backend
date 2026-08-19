<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserRoleManagementController extends Controller
{
    /**
     * Display list of users with their roles
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with(['roles'])
                ->whereNotIn('role', ['user', 'admin'])
                ->latest()
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_email', fn($data) => $data->email)

                ->addColumn('roles', function ($data) {
                    $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];

                    $hoverEffects = [
                        'hover-scale', 'hover-elevate-up', 'hover-elevate-down',
                        'hover-rotate-start', 'hover-rotate-end', 'hover-shadow-lg',
                        'hover-blur', 'hover-opacity-75', 'hover-skew-x', 'hover-brightness-125'
                    ];

                    $badges = $data->roles->map(function ($role) use ($colors, $data, $hoverEffects) {
                        $randomColor = $colors[array_rand($colors)];
                        $randomHoverEffect = $hoverEffects[array_rand($hoverEffects)];

                        return '<span class="badge badge-' . $randomColor . ' badge m-1 p-2 ' . $randomHoverEffect . '">
                    ' . e($role->name) . '
                                <button class="remove-role p-1" data-user="' . $data->id . '" data-role="' . $role->id . '"
                                        aria-label="Remove ' . e($role->name) . ' role" style="background: transparent; border: none; padding: 0; color: #ffffff; cursor: pointer;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </span>';
                            });

                    return $badges->implode(' ');
                })


                ->addColumn('permissions', function ($data) {
                    // Collect all permissions from roles
                    $rolePermissions = $data->roles->flatMap->permissions->pluck('name')->unique();

                    // Collect all direct permissions assigned to the user
                    $directPermissions = $data->permissions->pluck('name');

                    // Merge role-based and direct permissions and remove duplicates
                    $allPermissions = $rolePermissions->merge($directPermissions)->unique();

                    // Format permissions as HTML badges
                    return "<div id='permissions-{$data->id}'>" .
                        $allPermissions->map(fn($permission) =>
                        "<span class='badge bg-primary p-2'>{$permission}</span>"
                        )->implode(' ') .
                        "</div>";
                })
                ->addColumn('actions', function ($data) {
                return '
                <button type="button"
                            class="btn btn-primary hover:bg-black text-white px-3 py-1 rounded-md manage-role"
                            data-bs-toggle="modal"
                            data-bs-target="#role-modal"
                            data-user="' . $data->id . '"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="' . __('Manage roles for') . ' ' . htmlspecialchars($data->email) . '">
                        ' . __('Manage Roles') . '
                    </button>
                ';
                })

                ->rawColumns(['user_email','roles', 'permissions', 'actions'])
                ->make(true);
        }

        $roles = Role::all();
        return view('backend.layout.Permission.index', compact('roles'));
    }


    /**
     * Attach a role to a user.
     */
    public function attachRole(Request $request, $userId)
    {
        $request->validate(['role' => 'required|exists:roles,id']);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->role);

        if (!$user->hasRole($role->name)) {
            $user->assignRole($role->name);
            return response()->json(['success' => true, 'message' => 'Role attached successfully']);
        }

        return response()->json(['success' => false, 'message' => 'User already has this role']);
    }

    /**
     * Detach a role from a user.
     */
    public function detachRole(Request $request, $userId)
    {
        $request->validate(['role' => 'required|exists:roles,id']);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->role);

        if ($user->hasRole($role->name)) {
            $user->removeRole($role->name);
            return response()->json(['success' => true, 'message' => 'Role detached successfully']);
        }

        return response()->json(['success' => false, 'message' => 'User does not have this role']);
    }
}
