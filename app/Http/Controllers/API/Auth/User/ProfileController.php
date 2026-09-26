<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\BlockUserRequest;
use App\Http\Requests\Api\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Profile\DeleteAccountRequest;
use App\Http\Requests\Api\V1\Profile\SetLocaleRequest;
use App\Http\Requests\Api\V1\Profile\UnblockUserRequest;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Profile\BlockedUserResource;
use App\Http\Resources\Api\V1\Profile\getResource;
use App\Http\Resources\Api\V1\Profile\SetLocaleResource;
use App\Http\Resources\Api\V1\Profile\UpdateResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get user profile details
     */
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $auth = auth('api')->user();

            $message = translateHelper::translateCached(
                'profile.index.success',
                'User profile retrieved successfully.',
                $lang
            );

            return $this->sendResponse(new getResource($auth), $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(UpdateRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        $validated = $request->validated();

        try {
            $auth = auth('api')->user();

            if ($auth->role === Role::GUEST) {
                $message = translateHelper::translateCached(
                    'profile.update.guest_forbidden',
                    'Guest users cannot update their profile.',
                    $lang
                );
                return $this->sendError($message, [], 403);
            }

            // Handle avatar upload (Base64 cropped or File upload)
            if ($request->filled('avatar') && preg_match('/^data:image\/(\w+);base64,/', $request->avatar)) {
                if (!empty($auth->avatar)) {
                    helpers::deleteFile($auth->avatar, config('app.file_system'));
                }

                $avatarData = $request->avatar;
                if (preg_match('/^data:image\/(\w+);base64,/', $avatarData, $type)) {
                    $avatarData = substr($avatarData, strpos($avatarData, ',') + 1);
                    $type = strtolower($type[1]);

                    if (in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $avatarData = base64_decode($avatarData);
                        $fileName = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $type;
                        $filePath = 'uploads/avatar/' . $fileName;
                        \Illuminate\Support\Facades\Storage::disk(config('app.file_system'))->put($filePath, $avatarData);
                        $auth->avatar = $filePath;
                    }
                }
            } elseif ($request->hasFile('avatar')) {
                if (!empty($auth->avatar)) {
                    helpers::deleteFile($auth->avatar, config('app.file_system'));
                }

                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $auth->avatar = helpers::uploadFile('avatar', $file, $mediaFile, config('app.file_system'));
            }

            if (isset($validated['name'])) {
                $auth->name = $validated['name'];
            }
            if (isset($validated['email'])) {
                $auth->email = $validated['email'];
            }
            if (array_key_exists('province', $validated)) {
                $auth->province = $validated['province'];
            }
            if (array_key_exists('city', $validated)) {
                $auth->city = $validated['city'];
            }
            if (array_key_exists('address', $validated)) {
                $auth->address = $validated['address'];
            }
            $auth->save();

            $message = translateHelper::translateCached(
                'profile.update.success',
                'User profile updated successfully.',
                $lang
            );

            return $this->sendResponse(new UpdateResource($auth), $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!Hash::check($request->current_password, $user->password)) {
                $message = translateHelper::translateCached(
                    'profile.password.mismatch',
                    'Current password does not match.',
                    $lang
                );
                return $this->sendError($message, [], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            $message = translateHelper::translateCached(
                'profile.password.changed',
                'Password changed successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Block a user
     */
    public function blockUser(BlockUserRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            $targetUserId = (int) $request->input('user_id');

            if ($user->id === $targetUserId) {
                $message = translateHelper::translateCached(
                    'profile.block.self',
                    'You cannot block yourself.',
                    $lang
                );
                return $this->sendError($message, [], 422);
            }

            $blockedUsers = is_array($user->blocked_users) ? $user->blocked_users : [];

            // Check if already blocked
            $isAlreadyBlocked = false;
            foreach ($blockedUsers as $entry) {
                if (isset($entry['user_id']) && (int) $entry['user_id'] === $targetUserId) {
                    $isAlreadyBlocked = true;
                    break;
                }
            }

            if ($isAlreadyBlocked) {
                $message = translateHelper::translateCached(
                    'profile.block.already',
                    'User is already blocked.',
                    $lang
                );
                return $this->sendResponse([], $message);
            }

            $blockedUsers[] = [
                'user_id' => $targetUserId,
                'blocked_at' => now()->toIso8601String(),
            ];

            $user->blocked_users = $blockedUsers;
            $user->save();

            $message = translateHelper::translateCached(
                'profile.block.success',
                'User blocked successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Get list of blocked users
     */
    public function blockedUsersList(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            $blockedEntries = is_array($user->blocked_users) ? $user->blocked_users : [];

            if (empty($blockedEntries)) {
                $message = translateHelper::translateCached(
                    'profile.block.none_found',
                    'No blocked users found.',
                    $lang
                );
                return $this->sendResponse([], $message);
            }

            $blockedUserIds = array_column($blockedEntries, 'user_id');
            $blockedUsers = User::whereIn('id', $blockedUserIds)->get()->keyBy('id');

            $result = [];
            foreach ($blockedEntries as $entry) {
                $uid = $entry['user_id'];
                if ($blockedUsers->has($uid)) {
                    $blockedUser = $blockedUsers->get($uid);
                    $blockedUser->blocked_at = $entry['blocked_at'];
                    $result[] = $blockedUser;
                }
            }

            $message = translateHelper::translateCached(
                'profile.block.list_success',
                'Blocked users retrieved successfully.',
                $lang
            );

            return $this->sendResponse(BlockedUserResource::collection($result), $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Unblock a user
     */
    public function unblockUser(UnblockUserRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            $targetUserId = (int) $request->input('user_id');

            $blockedUsers = is_array($user->blocked_users) ? $user->blocked_users : [];

            $filtered = array_values(array_filter($blockedUsers, function ($entry) use ($targetUserId) {
                return isset($entry['user_id']) && (int) $entry['user_id'] !== $targetUserId;
            }));

            $user->blocked_users = $filtered;
            $user->save();

            $message = translateHelper::translateCached(
                'profile.unblock.success',
                'User unblocked successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Soft delete user account
     */
    public function deleteAccount(DeleteAccountRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();
            if (!Hash::check($request->password, $user->password)) {
                $message = translateHelper::translateCached(
                    'profile.delete.incorrect_password',
                    'Incorrect password.',
                    $lang
                );
                return $this->sendError($message, [], 422);
            }

            $user->status = 'pending_delete';
            $user->save();

            $message = translateHelper::translateCached(
                'profile.delete.pending',
                'Your account deletion request has been submitted successfully and is pending admin approval.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();

            $message = translateHelper::translateCached(
                'profile.notifications.get_success',
                'Notification settings retrieved successfully.',
                $lang
            );

            return $this->sendResponse([
                'all_notification' => $user->new_message && $user->marketing && $user->email_notification,
                'new_message' => (bool) $user->new_message,
                'marketing' => (bool) $user->marketing,
                'email_notification' => (bool) $user->email_notification,
            ], $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $user = auth('api')->user();

            if ($request->has('all_notification')) {
                $status = $request->boolean('all_notification');
                $user->new_message = $status;
                $user->marketing = $status;
                $user->email_notification = $status;
            } else {
                if ($request->has('new_message')) {
                    $user->new_message = $request->boolean('new_message');
                }
                if ($request->has('marketing')) {
                    $user->marketing = $request->boolean('marketing');
                }
                if ($request->has('email_notification')) {
                    $user->email_notification = $request->boolean('email_notification');
                }
            }

            $user->save();

            $message = translateHelper::translateCached(
                'profile.notifications.update_success',
                'Notification settings updated successfully.',
                $lang
            );

            return $this->sendResponse([
                'all_notification' => $user->new_message && $user->marketing && $user->email_notification,
                'new_message' => (bool) $user->new_message,
                'marketing' => (bool) $user->marketing,
                'email_notification' => (bool) $user->email_notification,
            ], $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            $message = translateHelper::translateCached(
                'profile.logout.success',
                'Successfully logged out.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (JWTException $e) {
            $message = translateHelper::translateCached(
                'profile.logout.failed',
                'Failed to log out, please try again later',
                $lang
            );
            return $this->sendError($message, [], 500);
        }
    }

    /**
     * Set user locale preference
     */
    public function setLocale(SetLocaleRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        try {
            $user = auth('api')->user();
            $user->locale = $validated['locale'];
            $user->save();

            app()->setLocale($validated['locale']);

            $message = translateHelper::translateCached(
                'profile.locale.success',
                'Language preference updated successfully.',
                $lang
            );

            return $this->sendResponse(new SetLocaleResource($user), $message);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Set user locale preference (publicly accessible POST route)
     */
    public function getSetLocale(Request $request)
    {
        $lang = $request->input(
            'lang',
            $request->input(
                'locale',
                $request->query('lang')
            )
        );

        if (!in_array($lang, ['en', 'ar'])) {
            $lang = 'en';
        }

        try {
            $user = auth('api')->user();
            if (!$user && $request->has('guest_token')) {
                try {
                    $user = JWTAuth::setToken($request->guest_token)->authenticate();
                } catch (Exception $e) {
                }
            }

            if ($user) {
                $user->locale = $lang;
                $user->save();
            }

            app()->setLocale($lang);

            $message = translateHelper::translateCached(
                'profile.locale.success',
                'Language preference updated successfully.',
                $lang
            );

            return $this->sendResponse(
                $user ? new SetLocaleResource($user) : null,
                $message
            );
        } catch (Exception $exception) {
            return $this->sendError(
                $exception->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Get list of supported languages
     */
    public function languagesList(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr'
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl'
            ]
        ];

        $message = translateHelper::translateCached(
            'profile.languages.retrieved',
            'Languages retrieved successfully.',
            $lang
        );

        return $this->sendResponse($languages, $message);
    }
}