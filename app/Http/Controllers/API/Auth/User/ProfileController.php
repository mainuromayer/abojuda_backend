<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Profile\DeleteAccountRequest;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Profile\BlockedUserResource;
use App\Http\Resources\Api\V1\Profile\getResource;
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
    public function index()
    {
        try {
            $auth = auth()->user();
            return $this->sendResponse(new getResource($auth), 'User profile retrieved successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(UpdateRequest $request)
    {
        $validated = $request->validated();

        try {
            $auth = auth()->user();

            if ($auth->role === Role::GUEST) {
                return $this->sendError('Guest users cannot update their profile.', [], 403);
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

            return $this->sendResponse(new UpdateResource($auth), 'User profile updated successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = auth()->user();
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->sendError('Current password does not match.', [], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->sendResponse([], 'Password changed successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Block a user
     */
    public function blockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = auth()->user();
            $targetUserId = (int)$request->input('user_id');

            if ($user->id === $targetUserId) {
                return $this->sendError('You cannot block yourself.', [], 422);
            }

            $blockedUsers = is_array($user->blocked_users) ? $user->blocked_users : [];

            // Check if already blocked
            $isAlreadyBlocked = false;
            foreach ($blockedUsers as $entry) {
                if (isset($entry['user_id']) && (int)$entry['user_id'] === $targetUserId) {
                    $isAlreadyBlocked = true;
                    break;
                }
            }

            if ($isAlreadyBlocked) {
                return $this->sendResponse([], 'User is already blocked.');
            }

            $blockedUsers[] = [
                'user_id' => $targetUserId,
                'blocked_at' => now()->toIso8601String(),
            ];

            $user->blocked_users = $blockedUsers;
            $user->save();

            return $this->sendResponse([], 'User blocked successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Get list of blocked users
     */
    public function blockedUsersList()
    {
        try {
            $user = auth()->user();
            $blockedEntries = is_array($user->blocked_users) ? $user->blocked_users : [];

            if (empty($blockedEntries)) {
                return $this->sendResponse([], 'No blocked users found.');
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

            return $this->sendResponse(BlockedUserResource::collection($result), 'Blocked users retrieved successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Soft delete user account
     */
    public function deleteAccount(DeleteAccountRequest $request)
    {
        try {
            $user = auth()->user();
            if (!Hash::check($request->password, $user->password)) {
                return $this->sendError('Incorrect password.', [], 422);
            }

            $user->status = 'pending_delete';
            $user->save();

            return $this->sendResponse([], 'Your account deletion request has been submitted successfully and is pending admin approval.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Get notification settings
     */
    public function getNotificationSettings()
    {
        try {
            $user = auth()->user();
            return $this->sendResponse([
                'all_notification'   => $user->new_message && $user->marketing && $user->email_notification,
                'new_message'        => (bool)$user->new_message,
                'marketing'          => (bool)$user->marketing,
                'email_notification' => (bool)$user->email_notification,
            ], 'Notification settings retrieved successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        try {
            $user = auth()->user();

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

            return $this->sendResponse([
                'all_notification'   => $user->new_message && $user->marketing && $user->email_notification,
                'new_message'        => (bool)$user->new_message,
                'marketing'          => (bool)$user->marketing,
                'email_notification' => (bool)$user->email_notification,
            ], 'Notification settings updated successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->sendResponse([], 'Successfully logged out.');
        } catch (JWTException $e) {
            return $this->sendError('Failed to log out, please try again later', [], 500);
        }
    }
}
