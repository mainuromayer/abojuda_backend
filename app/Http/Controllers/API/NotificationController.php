<?php

namespace App\Http\Controllers\API;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Notification\ClearAllNotificationsRequest;
use App\Http\Requests\Api\V1\Notification\DeleteNotificationRequest;
use App\Http\Requests\Api\V1\Notification\GetNotificationsRequest;
use App\Http\Requests\Api\V1\Notification\MarkAllReadNotificationRequest;
use App\Http\Requests\Api\V1\Notification\MarkReadNotificationRequest;
use App\Models\UserNotification;
use App\Traits\ApiResponse;
use Exception;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get the current request language ('ar' or 'en').
     */
    protected function getLocale($request): string
    {
        $locale = app()->getLocale();
        if (in_array($locale, ['en', 'ar'])) {
            return $locale;
        }

        $headerLang = $request->header('Accept-Language');
        if ($headerLang) {
            $short = strtolower(substr(trim($headerLang), 0, 2));
            if (in_array($short, ['en', 'ar'])) {
                return $short;
            }
        }

        return 'en';
    }

    /**
     * Get all notifications for the authenticated user with unread count and pagination.
     */
    public function index(GetNotificationsRequest $request)
    {
        $lang = $this->getLocale($request);

        try {
            $user = auth('api')->user();
            $perPage = (int) $request->input('per_page', 20);

            $notificationsQuery = UserNotification::where('user_id', $user->id)->latest();
            $paginator = $notificationsQuery->paginate($perPage);

            $unreadCount = UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            $formattedItems = collect($paginator->items())->map(function ($item) use ($lang) {
                // 1. Resolve localized title
                $title = null;
                if (method_exists($item, 'getTranslation')) {
                    $title = $item->getTranslation('title', $lang);
                }
                if (empty($title)) {
                    $title = is_array($item->title) ? ($item->title[$lang] ?? reset($item->title)) : (string) $item->title;
                }
                if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $title)) {
                    $title = __($title, [], 'ar') !== $title ? __($title, [], 'ar') : translateHelper::translateCached('notif.title.' . md5($title), $title, 'ar');
                } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', $title)) {
                    $title = __($title, [], 'en') !== $title ? __($title, [], 'en') : translateHelper::translateCached('notif.title.' . md5($title), $title, 'en');
                }

                // 2. Resolve localized message
                $message = null;
                if (method_exists($item, 'getTranslation')) {
                    $message = $item->getTranslation('message', $lang);
                }
                if (empty($message)) {
                    $message = is_array($item->message) ? ($item->message[$lang] ?? reset($item->message)) : (string) $item->message;
                }
                if ($lang === 'ar' && !preg_match('/\p{Arabic}/u', $message)) {
                    $message = __($message, [], 'ar') !== $message ? __($message, [], 'ar') : translateHelper::translateCached('notif.msg.' . md5($message), $message, 'ar');
                } elseif ($lang === 'en' && preg_match('/\p{Arabic}/u', $message)) {
                    $message = __($message, [], 'en') !== $message ? __($message, [], 'en') : translateHelper::translateCached('notif.msg.' . md5($message), $message, 'en');
                }

                // 3. Resolve localized time_ago
                $timeAgo = '';
                if ($item->created_at) {
                    $timeAgo = $item->created_at->locale($lang)->diffForHumans();
                }

                return [
                    'id'         => $item->id,
                    'type'       => $item->type,
                    'title'      => $title,
                    'message'    => $message,
                    'data'       => $item->data ?? [],
                    'is_read'    => (bool) $item->is_read,
                    'time_ago'   => $timeAgo,
                    'created_at' => $item->created_at ? $item->created_at->toIso8601String() : null,
                ];
            });

            $responseMsg = __('Notifications retrieved successfully.', [], $lang);

            return $this->sendResponse([
                'unread_count'  => $unreadCount,
                'notifications' => $formattedItems,
                'pagination'    => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'has_more'     => $paginator->hasMorePages(),
                ],
            ], $responseMsg);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(MarkReadNotificationRequest $request, $id)
    {
        $lang = $this->getLocale($request);

        try {
            $user = auth('api')->user();

            $notification = UserNotification::where('user_id', $user->id)->findOrFail($id);
            $notification->update(['is_read' => true]);

            $unreadCount = UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            $responseMsg = __('Notification marked as read.', [], $lang);

            return $this->sendResponse([
                'id'           => $notification->id,
                'is_read'      => true,
                'unread_count' => $unreadCount,
            ], $responseMsg);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllRead(MarkAllReadNotificationRequest $request)
    {
        $lang = $this->getLocale($request);

        try {
            $user = auth('api')->user();

            UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $responseMsg = __('All notifications marked as read.', [], $lang);

            return $this->sendResponse([
                'unread_count' => 0,
            ], $responseMsg);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Delete a single notification.
     */
    public function destroy(DeleteNotificationRequest $request, $id)
    {
        $lang = $this->getLocale($request);

        try {
            $user = auth('api')->user();

            $notification = UserNotification::where('user_id', $user->id)->findOrFail($id);
            $notification->delete();

            $unreadCount = UserNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            $responseMsg = __('Notification deleted successfully.', [], $lang);

            return $this->sendResponse([
                'unread_count' => $unreadCount,
            ], $responseMsg);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Clear all notifications for the authenticated user (Clear All button).
     */
    public function clearAll(ClearAllNotificationsRequest $request)
    {
        $lang = $this->getLocale($request);

        try {
            $user = auth('api')->user();

            UserNotification::where('user_id', $user->id)->delete();

            $responseMsg = __('All notifications cleared successfully.', [], $lang);

            return $this->sendResponse([
                'unread_count' => 0,
            ], $responseMsg);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
