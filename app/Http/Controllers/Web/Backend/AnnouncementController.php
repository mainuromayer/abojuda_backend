<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SendAnnouncementPushJob;
use App\Models\Announcement;
use App\Models\FirebaseToken;
use App\Models\User;
use App\Models\UserNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements and the form to send a new one.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Announcement::with('sender')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('title', function ($query, $keyword) {
                    $query->where('title', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('description', function ($query, $keyword) {
                    $query->where('description', 'LIKE', "%{$keyword}%");
                })
                ->addColumn('title', function ($row) {
                    return '<span class="fw-bold text-gray-800 fs-6">' . e($row->title) . '</span>';
                })
                ->addColumn('description', function ($row) {
                    $short = Str::limit(strip_tags($row->description), 80);
                    return '<span title="' . e($row->description) . '">' . e($short) . '</span>';
                })
                ->addColumn('recipients_count', function ($row) {
                    return '<span class="badge badge-light-primary fw-bold"><i class="fa-solid fa-users fs-7 me-1"></i>' . $row->recipients_count . ' ' . __('Users') . '</span>';
                })
                ->addColumn('sent_by', function ($row) {
                    return $row->sender ? e($row->sender->name ?? $row->sender->email) : __('Admin');
                })
                ->addColumn('created_at', function ($row) {
                    return helpers::translateDate($row->created_at, 'h:i a, Y M d') ?: '-';
                })
                ->addColumn('actions', function ($row) {
                    return '
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-icon btn-light-primary btn-sm view-announcement-btn"
                                data-title="' . e($row->title) . '"
                                data-desc="' . e($row->description) . '"
                                data-recipients="' . $row->recipients_count . '"
                                data-date="' . helpers::translateDate($row->created_at, 'h:i a, Y M d') . '"
                                title="' . __('View') . '">
                                <i class="fa-solid fa-eye fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-light-danger btn-sm delete-announcement-btn" data-id="' . $row->id . '" title="' . __('Delete') . '">
                                <i class="fa-solid fa-trash fs-6"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['title', 'description', 'recipients_count', 'actions'])
                ->make(true);
        }

        $totalAnnouncements = Announcement::count();

        // Calculate reachable active users count
        $reachableUsersCount = User::where('status', 'active')
            ->where('enable_notification', '!=', 0)
            ->count();

        // Calculate reachable active push devices (all users with enable_notification != 0 and active guest tokens)
        $reachableDevicesCount = FirebaseToken::where(function ($q) {
                $q->where('is_active', true)->orWhere('is_active', 1)->orWhere('is_active', '1');
            })
            ->where(function ($query) {
                $query->whereNull('user_id')
                      ->orWhereHas('user', function ($u) {
                          $u->where('status', 'active')
                            ->where('enable_notification', '!=', 0);
                      });
            })
            ->distinct('token')
            ->count('token');

        return view('backend.layout.Announcement.index', compact('totalAnnouncements', 'reachableUsersCount', 'reachableDevicesCount'));
    }

    /**
     * Send push notification announcement to all active mobile users.
     */
    public function send(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        try {
            // Active users who receive the announcement in their inbox
            $userIds = User::where('status', 'active')
                ->where('enable_notification', '!=', 0)
                ->pluck('id');
            $userCount = count($userIds);

            // Save Announcement record in admin history with total audience user count
            $announcement = Announcement::create([
                'title'            => $request->title,
                'description'      => $request->description,
                'sent_by'          => auth()->id(),
                'recipients_count' => $userCount,
            ]);

            // Save in-app UserNotification for each active user
            $titleAr = translateHelper::toArabic($request->title, 'en');
            $titleEn = translateHelper::toEnglish($request->title, 'ar');
            $descAr  = translateHelper::toArabic($request->description, 'en');
            $descEn  = translateHelper::toEnglish($request->description, 'ar');

            $inboxRecords = [];
            $now = now();
            foreach ($userIds as $uId) {
                $inboxRecords[] = [
                    'user_id'    => $uId,
                    'type'       => 'announcement',
                    'title'      => json_encode(['en' => $titleEn, 'ar' => $titleAr], JSON_UNESCAPED_UNICODE),
                    'message'    => json_encode(['en' => $descEn, 'ar' => $descAr], JSON_UNESCAPED_UNICODE),
                    'data'       => json_encode([
                        'announcement_id' => $announcement->id,
                        'type'            => 'announcement',
                    ]),
                    'is_read'    => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (!empty($inboxRecords)) {
                foreach (array_chunk($inboxRecords, 500) as $chunk) {
                    UserNotification::insert($chunk);
                }
            }

            // Dispatch background queue job to deliver mobile FCM push notifications one by one
            SendAnnouncementPushJob::dispatchAfterResponse(
                $announcement->id,
                $request->title,
                $request->description
            );

            // Count available mobile push tokens for info message
            $deviceTokensCount = FirebaseToken::where(function ($q) {
                    $q->where('is_active', true)->orWhere('is_active', 1)->orWhere('is_active', '1');
                })
                ->where(function ($query) {
                    $query->whereNull('user_id')
                          ->orWhereHas('user', function ($u) {
                              $u->where('status', 'active')
                                ->where('enable_notification', '!=', 0);
                          });
                })
                ->distinct('token')
                ->count('token');

            $msg = $deviceTokensCount > 0
                ? __('Announcement published! Queued push for :devices devices and delivered to :users user inboxes.', ['devices' => $deviceTokensCount, 'users' => $userCount])
                : __('Announcement published! Delivered to :users user inboxes (:devices push devices registered).', ['users' => $userCount, 'devices' => $deviceTokensCount]);

            return response()->json([
                'success'      => true,
                'message'      => $msg,
                'user_count'   => $userCount,
                'device_count' => $deviceTokensCount,
            ]);
        } catch (Exception $e) {
            Log::error('Announcement send failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Failed to send announcement: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an announcement record from history.
     */
    public function destroy($id)
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->delete();

            return response()->json([
                'success' => true,
                'message' => __('Announcement record deleted successfully.')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete announcement: ') . $e->getMessage()
            ], 500);
        }
    }
}
