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
                $user = User::where('email', $socialiteUser->getEmail())
                    ->where('provider', $provider)
                    ->where('provider_id', $socialiteUser->getId())
                    ->first();

                $isNewUser = false;

                if (!$user) {
                    $password = Str::random(16);
                    
                    $user = new User();
                    $user->name = $socialiteUser->getName() ?? "Social User";
                    $user->email = $socialiteUser->getEmail();
                    $user->provider = $provider;
                    $user->provider_id = $socialiteUser->getId();
                    $user->password = Hash::make($password);
                    $user->role = Role::USER;
                    $user->status = 'active';
                    $user->save();
                    
                    $isNewUser = true;
                }

                $token = auth('api')->login($user);
            }

            return $this->sendResponse(new SocialAuthResource($user), $isNewUser ? "User Register Successfully" : "User Login Successfully", 200, $token);
        } catch (Exception $exception) {
            return $this->sendError("Something Went Wrong: " . $exception->getMessage(), [], 500);
        }
    }
}
