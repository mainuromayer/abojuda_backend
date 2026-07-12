<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Role;
use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Mail\Api\V1\SendOtpMail;
use App\Mail\NotifyUser;
use App\Models\Report;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * list of user
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::whereNot('role', 'admin')->withCount('reportsAgainst')->latest();
 
            return DataTables::of($data)
                ->setRowClass(function ($row) {
                    return $row->status === 'pending_delete' ? 'bg-light-danger' : '';
                })
                ->addColumn('user', function ($row) {
                    $fullName = $row->name;
                    $avatarUrl = $row->avatar ? Helpers::generateTempURL($row->avatar,config('app.file_system')) : asset('backend/assets/media/avatars/300-1.jpg');
 
                    $statusBadge = '';
                    if ($row->status === 'pending_delete') {
                        $statusBadge = ' <span class="badge badge-light-danger text-uppercase fs-9">Delete Requested</span>';
                    } elseif ($row->status === 'suspended') {
                        $statusBadge = ' <span class="badge badge-light-warning text-uppercase fs-9">Suspended</span>';
                    }
 
                    return '
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <img src="' . $avatarUrl . '" class="w-100" alt="' . $fullName . '">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 mb-1">' . $fullName . $statusBadge . '</span>
                            <span>' . $row->email . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('joined_date', fn($row) => $row->created_at ? $row->created_at->format('d M Y, h:i a') : '')
                ->addColumn('reports', function ($row) {
                    $count = $row->reports_against_count ?? 0;
                    if ($count > 0) {
                        return '<button class="btn btn-sm btn-light-danger view-user-reports-btn" data-id="' . $row->id . '" data-name="' . htmlspecialchars($row->name) . '">' . $count . ' Report(s)</button>';
                    }
                    return '<span class="badge badge-light-secondary">No Reports</span>';
                })
                ->addColumn('actions', function ($row) {
                    $approveRejectButtons = '';
                    if ($row->status === 'pending_delete') {
                        $approveRejectButtons = '
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 approve-delete-btn" data-id="' . $row->id . '">Approve Delete</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 reject-delete-btn" data-id="' . $row->id . '">Reject Delete</a>
                            </div>
                        ';
                    }
                    $suspendButton = '';
                    if ($row->status === 'suspended') {
                        $suspendButton = '
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 dt-suspend-btn text-success" data-id="' . $row->id . '" data-action="unsuspend">Unsuspend</a>
                            </div>
                        ';
                    } else {
                        $suspendButton = '
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 dt-suspend-btn text-danger" data-id="' . $row->id . '" data-action="suspend">Suspend</a>
                            </div>
                        ';
                    }
                    return '
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                                   data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                     data-kt-menu="true">
                                    ' . $approveRejectButtons . '
                                    ' . $suspendButton . '
                                    <div class="menu-item px-3">
                                        <a href="#" data-id="' . $row->id . '" class="menu-link edit-user-btn px-3">Edit</a>
                                    </div>
                                    ' . ($row->status !== 'pending_delete' ? '
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-user-btn" data-id="' . $row->id . '">Delete</a>
                                    </div>
                                    ' : '') . '
                                </div>
                            </div>
                            ';
                })
                ->rawColumns(['user', 'actions', 'reports'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.User.index');

    }
    /**
     * store user data
     */
    public function store(Request $request)
    {
       $validData = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users'
            ],
            'province'   => ['required', 'string', 'max:255'],
            'city'       => ['required', 'string', 'max:255'],
            'address'    => ['nullable', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:8'],
            'avatar'     => ['nullable', 'file', 'max:2048', 'mimes:jpeg,jpg,png'],
            'role'       => ['required', new Enum(Role::class)],
        ]);

        try {
            // upload avatar
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $validData['avatar'] = Helpers::uploadFile('avatar',$file,$mediaFile,config('app.file_system')); // store url
            }

            // create user
            $user = User::create([
                'name'       => $validData['name'],
                'email'      => $validData['email'],
                'password'   => Hash::make($validData['password']),
                'province'   => $validData['province'],
                'city'       => $validData['city'],
                'address'    => $validData['address'] ?? null,
                'avatar'     => $validData['avatar'] ?? null,
                'role'       => $validData['role'],
                'status'     => 'active',
            ]);

            $title = "Welcome to " . config('app.name') . "! Your Account Has Been Created";

            $message = "Dear " . $user->name . ",\n\n"
                . "We’re thrilled to welcome you to " . config('app.name') . "! Your account has been successfully created with the following details:\n\n"
                . "- Email: " . $user->email . "\n"
                . "- Name: " . $user->name . "\n\n"
                . "You can now log in and start exploring all the features we have to offer. If you have any questions or need assistance, feel free to contact our support team.\n\n"
                . "Best regards,\n"
                . "The " . config('app.name') . " Team";

            // send mail to this user mail
            Mail::to($user->email)->send(new NotifyUser($title,$message));

            return redirect()->back()->with('success', 'User Created Successfully');
        }catch (\Exception $exception){
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * show edit data
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        if ($user)
        {
            $user->avatar = $user->avatar ? Helpers::generateTempURL($user->avatar,config('app.file_system')) : null; ;
        }
        return response()->json(['user' => $user]);
    }


    /**
     * user data update
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name'          => ['required', 'string', 'max:255'],
                'province'      => ['required', 'string', 'max:255'],
                'city'          => ['required', 'string', 'max:255'],
                'address'       => ['nullable', 'string', 'max:255'],
                'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
                'password'      => ['nullable', 'string', 'min:8'],
                'avatar'        => ['nullable', 'file', 'max:2048', 'mimes:jpeg,jpg,png'],
                'role'          => ['required', new Enum(Role::class)],
                'avatar_remove' => ['nullable', 'boolean'],
            ]);

            // Update user fields
            $user->name      = $validated['name'];
            $user->province  = $validated['province'];
            $user->city      = $validated['city'];
            $user->address   = $validated['address'];
            $user->email     = $validated['email'];
            $user->role      = $validated['role'];

            // Handle avatar
            if ($request->hasFile('avatar')) {
                // Delete existing avatar if present
                if ($user->avatar) {
                    Helpers::deleteFile($user->avatar, config('app.file_system'));
                }
                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $user->avatar = Helpers::uploadFile('avatar', $file, $mediaFile, config('app.file_system'));
            } elseif ($request->input('avatar_remove')) {
                if ($user->avatar) {
                    Helpers::deleteFile($user->avatar, config('app.file_system'));
                }
                $user->avatar = null;
            }

            // Update password only if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return response()->json(['message' => 'User updated successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed: ' . $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * delete user
     */
    public function delete($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
            }

            if ($user->role === 'admin') {
                return response()->json(['status' => 'error', 'message' => 'You cannot delete admin account']);
            }

            if ($user->status === 'pending_delete') {
                $user->status = 'active';
                $user->save();
                return response()->json(['status' => 'success', 'message' => 'User account deletion request rejected. User status set to active.']);
            }

            // Permanently delete user
            $user->delete();

            return response()->json(['status' => 'success', 'message' => 'User permanently deleted successfully.']);
        } catch (Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Approve user deletion request
     */
    public function approveDelete($id)
    {
        try {
            $user = User::findOrFail($id);
            // Permanently delete user
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User account deletion approved and permanently deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject user deletion request
     */
    public function rejectDelete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = 'active';
            $user->save();
            return response()->json(['success' => true, 'message' => 'User account deletion rejected. User status set to active.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get reports against a specific user
     */
    public function getReports($id)
    {
        try {
            $user = User::findOrFail($id);
            $reports = Report::where('reported_user_id', $id)->with('user')->latest()->get();

            // Mark matching notifications as read
            SystemNotification::where('is_read', false)
                ->where('target_url', url('/admin/user'))
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'user' => $user,
                'reports' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send custom email to a user from admin panel
     */
    public function sendCustomEmail(Request $request)
    {
        $request->validate([
            'email'   => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        try {
            $title = $request->subject;
            $body = $request->message;

            // Send notification email
            Mail::to($request->email)->send(new NotifyUser($title, $body));

            return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationRead($id)
    {
        try {
            $notification = SystemNotification::findOrFail($id);
            $notification->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all unread notifications as read
     */
    public function markAllNotificationsRead()
    {
        try {
            SystemNotification::where('is_read', false)->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a single report as read
     */
    public function markReportRead($id)
    {
        try {
            $report = Report::findOrFail($id);
            $report->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'Report marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all reports against a user as read
     */
    public function markAllReportsRead($userId)
    {
        try {
            Report::where('reported_user_id', $userId)->where('is_read', false)->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'All reports marked as read.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Suspend a user account
     */
    public function suspend($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->hasRole(Role::ADMIN->value)) {
                return response()->json(['status' => 'error', 'message' => 'You cannot suspend an admin account.']);
            }

            $user->status = 'suspended';
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'User account suspended successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Unsuspend a user account
     */
    public function unsuspend($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = 'active';
            $user->save();

            return response()->json(['status' => 'success', 'message' => 'User account unsuspended successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete a single report
     */
    public function deleteReport($id)
    {
        try {
            $report = Report::findOrFail($id);
            $report->delete();

            return response()->json(['success' => true, 'message' => 'Report deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete reports
     */
    public function bulkDeleteReports(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:reports,id']
        ]);

        try {
            Report::whereIn('id', $request->ids)->delete();

            return response()->json(['success' => true, 'message' => 'Selected reports deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Clear all notifications
     */
    public function clearNotifications()
    {
        try {
            SystemNotification::query()->delete();

            return response()->json(['success' => true, 'message' => 'All notifications cleared successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
