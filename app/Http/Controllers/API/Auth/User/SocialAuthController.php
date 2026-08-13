<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
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
        $validated = $request->validated();

        try {
            $provider      = $validated['provider'];
            $socialiteUser = Socialite::driver($provider)->stateless()->userFromToken($validated['token']);

            if ($socialiteUser) {
                // 1. Try to find the user by provider and provider_id
                $user = User::where('provider', $provider)
                    ->where('provider_id', $socialiteUser->getId())
                    ->first();

                $isNewUser = false;

                if (!$user) {
                    // 2. Check if a user already exists with the same email
                    $user = User::where('email', $socialiteUser->getEmail())->first();

                    if ($user) {
                        // Link the provider details to the existing email account
                        $user->provider = $provider;
                        $user->provider_id = $socialiteUser->getId();
                        if (empty($user->avatar)) {
                            $user->avatar = $socialiteUser->getAvatar();
                        }
                        $user->save();
                    } else {
                        // 3. Create a brand new user
                        $password = Str::random(16);
                        
                        $user = new User();
                        $user->name = $socialiteUser->getName() ?? "Social User";
                        $user->email = $socialiteUser->getEmail();
                        $user->avatar = $socialiteUser->getAvatar();
                        $user->provider = $provider;
                        $user->provider_id = $socialiteUser->getId();
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
            }

            return $this->sendResponse(new SocialAuthResource($user), $isNewUser ? "User Register Successfully" : "User Login Successfully", 200, $token);
        } catch (Exception $exception) {
            return $this->sendError(__('Something Went Wrong:') . ' ' . $exception->getMessage(), [], 500);
        }
    }
}
