<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Mail\NotifyUser;
use App\Models\ContactSupport;
use App\Models\SystemNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ContactSupportController extends Controller
{
    /**
     * Display a listing of contact support requests.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ContactSupport::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('email', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('subject', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('subject->en', 'LIKE', "%{$keyword}%")
                          ->orWhere('subject->ar', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('describe_issue', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('describe_issue->en', 'LIKE', "%{$keyword}%")
                          ->orWhere('describe_issue->ar', 'LIKE', "%{$keyword}%");
                    });
                })
                ->addColumn('email', function ($row) {
                    return '<a href="mailto:' . e($row->email) . '" class="text-gray-800 text-hover-primary fw-semibold"><i class="ki-duotone ki-sms fs-6 me-1 text-primary"></i>' . e($row->email) . '</a>';
                })
                ->addColumn('subject', function ($row) {
                    $subject = $row->subject ? __($row->subject) : 'No Subject';
                    return '<span class="fw-bold text-gray-800">' . e($subject) . '</span>';
                })
                ->addColumn('describe_issue', function ($row) {
                    $text = $row->describe_issue ? __($row->describe_issue) : '-';
                    $short = Str::limit(strip_tags($text), 70);
                    return '<span title="' . e($text) . '">' . e($short) . '</span>';
                })
                ->addColumn('attachment', function ($row) {
                    if (!$row->attachment) {
                        return '<span class="badge badge-light-secondary text-muted fs-8">' . __('No File') . '</span>';
                    }
                    $url = helpers::generateTempURL($row->attachment, config('app.file_system', 'public')) ?? asset($row->attachment);
                    return '<a href="' . $url . '" target="_blank" class="badge badge-light-primary text-hover-primary fs-8">
                                <i class="ki-duotone ki-file fs-7 me-1 text-primary"></i> ' . __('View Attachment') . '
                            </a>';
                })
                ->addColumn('created_at', function ($row) {
                    return helpers::translateDate($row->created_at, 'h:i a, Y M d') ?: '-';
                })
                ->addColumn('actions', function ($row) {
                    return '
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-icon btn-light-primary btn-sm view-support-btn" data-id="' . $row->id . '" title="' . __('View Details') . '">
                                <i class="fa-solid fa-eye fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-light-info btn-sm reply-support-btn" data-id="' . $row->id . '" data-email="' . e($row->email) . '" data-subject="' . e($row->subject ? __($row->subject) : '') . '" title="' . __('Reply via Email') . '">
                                <i class="fa-solid fa-reply fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-light-danger btn-sm delete-support-btn" data-id="' . $row->id . '" title="' . __('Delete') . '">
                                <i class="fa-solid fa-trash fs-6"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['email', 'subject', 'describe_issue', 'attachment', 'actions'])
                ->make(true);
        }

        $totalSupports = ContactSupport::count();
        $targetId = $request->query('id');

        // Mark matching unread notification as read if arrived via targetId
        if ($targetId) {
            $targetSupport = ContactSupport::find($targetId);
            SystemNotification::where('type', 'contact_support')
                ->where('is_read', false)
                ->where(function ($q) use ($targetId, $targetSupport) {
                    $q->where('target_url', 'LIKE', "%id={$targetId}%");
                    if ($targetSupport) {
                        $q->orWhere('content', 'LIKE', "%{$targetSupport->email}%");
                    }
                })
                ->update(['is_read' => true]);
        }

        return view('backend.layout.ContactSupport.index', compact('totalSupports', 'targetId'));
    }

    /**
     * Get details of a single contact support request.
     */
    public function show($id)
    {
        try {
            $support = ContactSupport::findOrFail($id);

            // Mark any unread system notification related to this support as read
            SystemNotification::where('type', 'contact_support')
                ->where('is_read', false)
                ->where(function ($q) use ($support) {
                    $q->where('content', 'LIKE', "%{$support->email}%")
                      ->orWhere('target_url', 'LIKE', "%id={$support->id}%");
                })
                ->update(['is_read' => true]);

            $attachmentUrl = null;
            if ($support->attachment) {
                $attachmentUrl = helpers::generateTempURL($support->attachment, config('app.file_system', 'public')) ?? asset($support->attachment);
            }

            // Safely get subject
            $subjectVal = '';
            if (method_exists($support, 'getTranslation')) {
                $subjectVal = $support->getTranslation('subject', app()->getLocale()) ?: $support->getTranslation('subject', 'en');
            }
            if (empty($subjectVal)) {
                $subjectVal = is_array($support->subject) ? ($support->subject[app()->getLocale()] ?? reset($support->subject)) : (string) ($support->subject ?? '');
            }

            // Safely get describe_issue
            $issueVal = '';
            if (method_exists($support, 'getTranslation')) {
                $issueVal = $support->getTranslation('describe_issue', app()->getLocale()) ?: $support->getTranslation('describe_issue', 'en');
            }
            if (empty($issueVal)) {
                $issueVal = is_array($support->describe_issue) ? ($support->describe_issue[app()->getLocale()] ?? reset($support->describe_issue)) : (string) ($support->describe_issue ?? '');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $support->id,
                    'email' => $support->email,
                    'subject' => $subjectVal ?: 'No Subject',
                    'describe_issue' => $issueVal,
                    'attachment' => $attachmentUrl,
                    'attachment_raw' => $support->attachment,
                    'created_at' => helpers::translateDate($support->created_at, 'h:i a, Y M d'),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Support request not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Send email reply to the contact requester.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        try {
            $support = ContactSupport::findOrFail($id);

            Mail::to($support->email)->send(new NotifyUser($request->subject, $request->message));

            return response()->json([
                'success' => true,
                'message' => __('Reply sent successfully to :email', ['email' => $support->email])
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reply to contact support: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('Failed to send reply: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified contact support request.
     */
    public function destroy($id)
    {
        try {
            $support = ContactSupport::findOrFail($id);

            if ($support->attachment) {
                helpers::deleteFile($support->attachment, config('app.file_system', 'public'));
            }

            $support->delete();

            return response()->json([
                'success' => true,
                'message' => __('Support request deleted successfully.')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete support request: ') . $e->getMessage()
            ], 500);
        }
    }
}
