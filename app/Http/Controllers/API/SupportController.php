<?php

namespace App\Http\Controllers\API;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Support\ContactSupportRequest;
use App\Mail\SupportRequestMail;
use App\Models\ContactSupport;
use App\Models\SystemNotification;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    use ApiResponse;

    public function contactSupport(ContactSupportRequest $request)
    {
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $fileName = time() . '_support_' . Str::random(10) . '.' . $request->file('attachment')->getClientOriginalExtension();
                $attachmentPath = helpers::uploadFile('support', $request->file('attachment'), $fileName, config('app.file_system'));
            }

            $support = ContactSupport::create([
                'email' => $request->email,
                'subject' => $request->subject,
                'describe_issue' => $request->describe_issue,
                'attachment' => $attachmentPath,
            ]);

            // Create Admin System Notification
            SystemNotification::create([
                'type' => 'contact_support',
                'title' => 'New Support Request',
                'content' => 'Support request from ' . $request->email . '. Issue: ' . Str::limit($request->describe_issue, 100),
                'is_read' => false,
            ]);

            // Send Email to Admin(s)
            $adminEmails = User::where('role', 'admin')->pluck('email')->toArray();
            if (empty($adminEmails)) {
                $adminEmails = ['admin@gmail.com'];
            }

            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new SupportRequestMail(
                    $request->email,
                    $request->subject,
                    $request->describe_issue,
                    $attachmentPath
                ));
            }

            return $this->sendResponse($support, 'Support request submitted successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to submit support request.', ['error' => $e->getMessage()], 500);
        }
    }
}
