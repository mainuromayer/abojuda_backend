<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Report\ReportPostRequest;
use App\Http\Requests\Api\V1\Report\ReportUserRequest;
use App\Models\Post;
use App\Models\Report;
use App\Models\SystemNotification;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;

class ReportController extends Controller
{
    use ApiResponse;

    public function reportUser(ReportUserRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $reportedUser = User::findOrFail($request->reported_user_id);

            if ($user->id === $reportedUser->id) {
                return $this->sendError('You cannot report yourself.', [], 400);
            }

            $report = Report::create([
                'user_id' => $user->id,
                'reported_user_id' => $reportedUser->id,
                'report_type' => 'user',
                'reason' => $request->reason,
                'other_note' => $request->other_note,
            ]);

            // Create Admin System Notification
            SystemNotification::create([
                'type' => 'report_user',
                'title' => 'User Reported',
                'content' => 'User "' . $user->name . '" reported user "' . $reportedUser->name . '". Reason: ' . $request->reason,
                'is_read' => false,
                'target_url' => url('/admin/user?reported_user_id=' . $reportedUser->id),
            ]);

            return $this->sendResponse($report, 'User reported successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to report user.', ['error' => $e->getMessage()], 500);
        }
    }

    public function reportPost(ReportPostRequest $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            $post = Post::findOrFail($request->post_id);

            if ($user->id === $post->user_id) {
                return $this->sendError('You cannot report your own post.', [], 400);
            }

            $report = Report::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'report_type' => 'post',
                'reason' => $request->reason,
                'other_note' => $request->other_note,
            ]);

            // Create Admin System Notification
            SystemNotification::create([
                'type' => 'report_post',
                'title' => 'Post Reported',
                'content' => 'User "' . $user->name . '" reported listing "' . ($post->title ?? $post->business_name) . '". Reason: ' . $request->reason,
                'is_read' => false,
                'target_url' => url('/admin/posts?reported_post_id=' . $post->id),
            ]);

            return $this->sendResponse($report, 'Post reported successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to report post.', ['error' => $e->getMessage()], 500);
        }
    }
}
