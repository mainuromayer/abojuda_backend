<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\OtpVerifyRequest;
use App\Http\Requests\Api\V1\Auth\SignupRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Http\Resources\Api\V1\Auth\GuestResource;
use App\Http\Resources\Api\V1\Auth\ForgotPasswordResource;
use App\Http\Resources\Api\V1\Auth\VerifyOtpResource;
use App\Http\Resources\Api\V1\Auth\ResetPasswordResource;
use App\Http\Resources\Api\V1\Auth\ResendOtpResource;
use App\Mail\Api\V1\SendOtpMail;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use ApiResponse;

    public function signUp(SignupRequest $request)
    {
        try {
            $code = rand(1000, 9999);
            $email = $request->validated()['email'];

            Cache::put("verification_code_{$email}", $code, 600);
            Cache::put("signup_data_{$email}", $request->validated(), 600);

            Mail::to($email)->send(new SendOtpMail($code));

            return $this->sendResponse([], 'A verification code has been sent to your email address.'.$code, 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function verifyOtp(OtpVerifyRequest $request)
    {
        $email = $request->email;
        $otp = (int) $request->otp;
        $type = $request->type;

        if ($type === 'signup') {
            $cachedCode = Cache::get("verification_code_{$email}");
            $cachedData = Cache::get("signup_data_{$email}");

            if (!$cachedCode || $otp !== $cachedCode) {
                return $this->sendError("The OTP you entered is incorrect or has expired. Please try again.", [], 400);
            }

            if (!$cachedData) {
                return $this->sendError("Your registration session has expired. Please sign up again.", [], 400);
            }

            try {
                $user = new User();
                $user->name = $cachedData['name'];
                $user->email = $cachedData['email'];
                $user->password = Hash::make($cachedData['password']);
                $user->province = $cachedData['province'] ?? null;
                $user->city = $cachedData['city'] ?? null;
                $user->role = Role::USER;
                $user->status = 'active';
                $user->save();

                $token = auth('api')->login($user);

                Cache::forget("verification_code_{$email}");
                Cache::forget("signup_data_{$email}");

                return $this->sendResponse(new UserResource($user), 'User registration successful.', 200, $token);
            } catch (Exception $e) {
                return $this->sendError($e->getMessage(), [], 500);
            }
        } else {
            if (!User::where('email', $email)->exists()) {
                return $this->sendError('No account found with this email address.', [], 404);
            }

            $cachedCode = Cache::get("password_reset_code_{$email}");

            if (!$cachedCode || $otp !== $cachedCode) {
                return $this->sendError('The OTP you entered is incorrect or has expired.', [], 400);
            }

            Cache::put("password_reset_verified_{$email}", true, 600);
            Cache::forget("password_reset_code_{$email}");

            return $this->sendResponse(new VerifyOtpResource(['email' => $email]), 'OTP verified successfully. You can now reset your password.', 200);
        }
    }
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();

        if (!$token = JWTAuth::attempt($validatedData)) {
            return $this->sendError('Invalid credentials. Please check your email and password.', [], 401);
        }

        $user = Auth::user();

        if ($user->status === 'suspended') {
            auth('api')->logout();
            return $this->sendError('Your account has been suspended. Please contact support.', [], 403);
        }

        return $this->sendResponse(new UserResource($user), 'User logged in successfully.', 200, $token);
    }

    public function guestLogin()
    {
        try {
            $randomString = Str::random(10);
            
            $guestUser = new User();
            $guestUser->name = 'Guest User';
            $guestUser->email = null;
            $guestUser->password = Hash::make($randomString);
            $guestUser->role = Role::GUEST;
            $guestUser->guest_expires_at = now()->addWeek()->timestamp;
            $guestUser->status = 'active';
            $guestUser->save();

            $token = auth('api')->setTTL(10080)->login($guestUser);

            $guestUser->guest_token = $token;
            $guestUser->save();

            return $this->sendResponse(new GuestResource($guestUser), 'Guest user registered successfully.', 200, $token);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $email = $request->email;
            $code = rand(1000, 9999);

            Cache::put("password_reset_code_{$email}", $code, 600);

            Mail::to($email)->send(new SendOtpMail($code));

            return $this->sendResponse(new ForgotPasswordResource(['email' => $email]), 'Verification code has been sent to your email address. ' . $code, 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $email = $request->email;

        if (!Cache::get("password_reset_verified_{$email}")) {
            return $this->sendError('Unauthorized action. Please verify your OTP first.', [], 403);
        }

        try {
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            Cache::forget("password_reset_verified_{$email}");

            return $this->sendResponse(new ResetPasswordResource(['email' => $email]), 'Password has been reset successfully.', 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $email = $request->email;
        $type = $request->type;

        try {
            $code = rand(1000, 9999);

            if ($type === 'signup') {
                if (User::where('email', $email)->exists()) {
                    return $this->sendError('This email is already registered.', [], 400);
                }

                $signupData = Cache::get("signup_data_{$email}");
                if (!$signupData) {
                    return $this->sendError('Your registration session has expired. Please sign up again.', [], 400);
                }

                Cache::put("verification_code_{$email}", $code, 600);
            } else {
                if (!User::where('email', $email)->exists()) {
                    return $this->sendError('No account found with this email address.', [], 404);
                }

                Cache::put("password_reset_code_{$email}", $code, 600);
            }

            Mail::to($email)->send(new SendOtpMail($code));

            return $this->sendResponse(new ResendOtpResource(['email' => $email, 'type' => $type]), 'Verification code has been resent to your email address. ' . $code, 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
