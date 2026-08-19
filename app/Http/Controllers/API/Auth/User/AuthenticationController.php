<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Actions\GeoLocation;
use App\Enum\Role;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\OtpVerifyRequest;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\SignupRequest;
use App\Http\Resources\Api\V1\Auth\ForgotPasswordResource;
use App\Http\Resources\Api\V1\Auth\GuestResource;
use App\Http\Resources\Api\V1\Auth\ResendOtpResource;
use App\Http\Resources\Api\V1\Auth\ResetPasswordResource;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Http\Resources\Api\V1\Auth\VerifyOtpResource;
use App\Mail\Api\V1\SendOtpMail;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use ApiResponse;

    public function signUp(SignupRequest $request)
    {
        try {
            $lang = $request->header('Accept-Language', 'en');
            $validated = $request->validated();

            $email = strtolower(trim($validated['email']));
            $code = (string) random_int(1000, 9999);
            $expiresAt = now()->addMinutes(10);

            $name = translateHelper::toEnglish(
                $validated['name'] ?? '',
                $lang
            );

            $province = isset($validated['province'])
                ? translateHelper::toEnglish($validated['province'], $lang)
                : null;

            $city = isset($validated['city'])
                ? translateHelper::toEnglish($validated['city'], $lang)
                : null;

            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $validated['password'],
                'province' => $province,
                'city' => $city,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ];

            Cache::put(
                "verification_code_{$email}",
                $code,
                $expiresAt
            );

            Cache::put(
                "signup_data_{$email}",
                $data,
                $expiresAt
            );

            Mail::to($email)->send(
                new SendOtpMail($code)
            );

            $message = translateHelper::translateCached(
                'auth.signup.otp_sent',
                'A verification code has been sent to your email address.',
                $lang
            );

            return $this->sendResponse(
                [],
                $code . ' . ' . $message,
                200
            );
        } catch (Exception $exception) {
            Log::error('Sign Up Error', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->sendError(
                $exception->getMessage(),
                [],
                500
            );
        }
    }

    public function verifyOtp(OtpVerifyRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $email = strtolower(trim($request->email));
        $otp = trim((string) $request->otp);
        $type = $request->type;

        if ($type === 'signup') {
            $otpKey = "verification_code_{$email}";
            $signupDataKey = "signup_data_{$email}";

            $cachedCode = Cache::get($otpKey);
            $cachedData = Cache::get($signupDataKey);

            if (!$cachedCode || !hash_equals((string) $cachedCode, $otp)) {
                $message = translateHelper::translateCached(
                    'auth.otp.invalid',
                    'The OTP you entered is incorrect or has expired. Please try again.',
                    $lang
                );

                return $this->sendError(
                    $message,
                    [],
                    400
                );
            }

            if (!$cachedData) {
                $message = translateHelper::translateCached(
                    'auth.signup.session_expired',
                    'Your registration session has expired. Please sign up again.',
                    $lang
                );

                return $this->sendError(
                    $message,
                    [],
                    400
                );
            }

            try {
                $user = User::create([
                    'name' => $cachedData['name'],
                    'email' => $cachedData['email'],
                    'password' => Hash::make($cachedData['password']),
                    'province' => $cachedData['province'] ?? null,
                    'city' => $cachedData['city'] ?? null,
                    'latitude' => $cachedData['latitude'] ?? null,
                    'longitude' => $cachedData['longitude'] ?? null,
                    'role' => Role::USER,
                    'status' => 'active',
                ]);

                if (is_null($user->latitude) || is_null($user->longitude)) {
                    if ($user->city || $user->province) {
                        $address = implode(', ', array_filter([
                            $user->city,
                            $user->province,
                        ]));

                        $coords = app(GeoLocation::class)->getCoordinates($address);

                        $user->update([
                            'latitude' => $coords['latitude'] ?? null,
                            'longitude' => $coords['longitude'] ?? null,
                        ]);
                    }
                }

                $token = auth('api')->login($user);

                Cache::forget($otpKey);
                Cache::forget($signupDataKey);

                $message = translateHelper::translateCached(
                    'auth.signup.success',
                    'User registration successful.',
                    $lang
                );

                return $this->sendResponse(
                    new UserResource($user),
                    $message,
                    200,
                    $token
                );
            } catch (Exception $e) {
                Log::error('Signup User Creation Error', [
                    'email' => $email,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return $this->sendError(
                    $e->getMessage(),
                    [],
                    500
                );
            }
        }

        if (!User::where('email', $email)->exists()) {
            $message = translateHelper::translateCached(
                'auth.email.not_found',
                'No account found with this email address.',
                $lang
            );

            return $this->sendError(
                $message,
                [],
                404
            );
        }

        $otpKey = "password_reset_code_{$email}";
        $cachedCode = Cache::get($otpKey);

        if (!$cachedCode || !hash_equals((string) $cachedCode, $otp)) {
            $message = translateHelper::translateCached(
                'auth.otp.invalid_short',
                'The OTP you entered is incorrect or has expired.',
                $lang
            );

            return $this->sendError(
                $message,
                [],
                400
            );
        }

        Cache::put(
            "password_reset_verified_{$email}",
            true,
            now()->addMinutes(10)
        );

        Cache::forget($otpKey);

        $message = translateHelper::translateCached(
            'auth.otp.verified',
            'OTP verified successfully. You can now reset your password.',
            $lang
        );

        return $this->sendResponse(
            new VerifyOtpResource([
                'email' => $email,
            ]),
            $message,
            200
        );
    }

    public function login(LoginRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validatedData = $request->validated();

        if (!$token = JWTAuth::attempt($validatedData)) {
            $message = translateHelper::translateCached(
                'auth.login.invalid_credentials',
                'Invalid credentials. Please check your email and password.',
                $lang
            );
            return $this->sendError($message, [], 401);
        }

        $user = Auth::user();

        if ($user->status === 'suspended') {
            auth('api')->logout();
            $message = translateHelper::translateCached(
                'auth.account.suspended',
                'Your account has been suspended. Please contact support.',
                $lang
            );
            return $this->sendError($message, [], 403);
        }

        $message = translateHelper::translateCached(
            'auth.login.success',
            'User logged in successfully.',
            $lang
        );

        return $this->sendResponse(new UserResource($user), $message, 200, $token);
    }

    public function guestLogin(\Illuminate\Http\Request $request)
    {
        try {
            $lang = $request->header('Accept-Language', 'en');
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

            $message = translateHelper::translateCached(
                'auth.guest.success',
                'Guest user registered successfully.',
                $lang
            );

            return $this->sendResponse(new GuestResource($guestUser), $message, 200, $token);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $lang = $request->header('Accept-Language', 'en');
            $email = $request->email;
            $code = rand(1000, 9999);

            Cache::put("password_reset_code_{$email}", $code, 600);

            Mail::to($email)->send(new SendOtpMail($code));

            $message = translateHelper::translateCached(
                'auth.password.reset_code_sent',
                'Verification code has been sent to your email address.',
                $lang
            );

            return $this->sendResponse(new ForgotPasswordResource(['email' => $email]), $message . ' ' . $code, 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $email = $request->email;

        if (!Cache::get("password_reset_verified_{$email}")) {
            $message = translateHelper::translateCached(
                'auth.password.unauthorized',
                'Unauthorized action. Please verify your OTP first.',
                $lang
            );
            return $this->sendError($message, [], 403);
        }

        try {
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            Cache::forget("password_reset_verified_{$email}");

            $message = translateHelper::translateCached(
                'auth.password.reset_success',
                'Password has been reset successfully.',
                $lang
            );

            return $this->sendResponse(new ResetPasswordResource(['email' => $email]), $message, 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $email = $request->email;
        $type = $request->type;

        try {
            $code = rand(1000, 9999);

            if ($type === 'signup') {
                if (User::where('email', $email)->exists()) {
                    $message = translateHelper::translateCached(
                        'auth.email.already_registered',
                        'This email is already registered.',
                        $lang
                    );
                    return $this->sendError($message, [], 400);
                }

                $signupData = Cache::get("signup_data_{$email}");
                if (!$signupData) {
                    $message = translateHelper::translateCached(
                        'auth.signup.session_expired',
                        'Your registration session has expired. Please sign up again.',
                        $lang
                    );
                    return $this->sendError($message, [], 400);
                }

                Cache::put("verification_code_{$email}", $code, 600);
            } else {
                if (!User::where('email', $email)->exists()) {
                    $message = translateHelper::translateCached(
                        'auth.email.not_found',
                        'No account found with this email address.',
                        $lang
                    );
                    return $this->sendError($message, [], 404);
                }

                Cache::put("password_reset_code_{$email}", $code, 600);
            }

            Mail::to($email)->send(new SendOtpMail($code));

            $message = translateHelper::translateCached(
                'auth.otp.resent',
                'Verification code has been resent to your email address.',
                $lang
            );

            return $this->sendResponse(new ResendOtpResource(['email' => $email, 'type' => $type]), $message . ' ' . $code, 200);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}