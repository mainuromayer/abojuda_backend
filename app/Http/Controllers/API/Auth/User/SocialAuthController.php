<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SocialAuthRequest;
use App\Http\Resources\Api\V1\Auth\SocialAuthResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    use ApiResponse;

    public function SocialLogin(SocialAuthRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        try {
            $provider      = $validated['provider'];
            $socialiteUser = Socialite::driver($provider)->stateless()->userFromToken($validated['token']);

            $isNewUser = false;

            if ($socialiteUser) {
                $socialEmail = $socialiteUser->getEmail();
                $providerId  = $socialiteUser->getId();

                if (!empty($socialEmail)) {
                    $trashedUser = User::withTrashed()->where('email', $socialEmail)->first();
                    if ($trashedUser && $trashedUser->trashed()) {
                        $deletedMsg = translateHelper::translateCached('social_auth.account_deleted', 'This account has been deleted and cannot be logged in.', $lang);
                        return $this->sendError($deletedMsg, [], 403);
                    }
                }

                if (!empty($providerId)) {
                    $trashedByProvider = User::withTrashed()
                        ->where('provider', $provider)
                        ->where('provider_id', $providerId)
                        ->first();
                    if ($trashedByProvider && $trashedByProvider->trashed()) {
                        $deletedMsg = translateHelper::translateCached('social_auth.account_deleted', 'This account has been deleted and cannot be logged in.', $lang);
                        return $this->sendError($deletedMsg, [], 403);
                    }
                }

                // 1. Try to find the user by provider and provider_id
                $user = null;
                if (!empty($providerId)) {
                    $user = User::where('provider', $provider)
                        ->where('provider_id', $providerId)
                        ->first();
                }

                if (!$user) {
                    // 2. Check if a user already exists with the same email
                    if (!empty($socialEmail)) {
                        $user = User::where('email', $socialEmail)->first();
                    }

                    if ($user) {
                        // Link the provider details to the existing email account
                        $user->provider = $provider;
                        $user->provider_id = $providerId;
                        if (empty($user->avatar)) {
                            $user->avatar = $socialiteUser->getAvatar();
                        }
                        $user->save();
                    } else {
                        // 3. Create a brand new user
                        $password = Str::random(16);

                        $user = new User();
                        $user->name = $socialiteUser->getName() ?? "Social User";
                        $user->email = $socialEmail;
                        $user->avatar = $socialiteUser->getAvatar();
                        $user->provider = $provider;
                        $user->provider_id = $providerId;
                        $user->password = Hash::make($password);
                        $user->role = Role::USER;
                        $user->status = 'active';
                        $user->save();

                        $isNewUser = true;
                    }
                } else {
                    // Update avatar if it was empty
                    if (empty($user->avatar) && $socialiteUser->getAvatar()) {
                        $user->avatar = $socialiteUser->getAvatar();
                        $user->save();
                    }
                }

                $token = auth('api')->login($user);
            } else {
                $message = translateHelper::translateCached(
                    'social_auth.failed',
                    'Unable to authenticate with the selected provider.',
                    $lang
                );
                return $this->sendError($message, [], 401);
            }

            $messageKey = $isNewUser ? 'social_auth.register_success' : 'social_auth.login_success';
            $messageDefault = $isNewUser ? 'User Register Successfully' : 'User Login Successfully';
            $message = translateHelper::translateCached($messageKey, $messageDefault, $lang);

            return $this->sendResponse(new SocialAuthResource($user), $message, 200, $token);
        } catch (Exception $exception) {
            $prefix = translateHelper::translateCached('social_auth.error_prefix', 'Something Went Wrong:', $lang);
            return $this->sendError($prefix . ' ' . $exception->getMessage(), [], 500);
        }
    }
}