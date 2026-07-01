<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Profile\getResource;
use App\Http\Resources\Api\V1\Profile\UpdateResource;
use App\Traits\ApiResponse;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $auth = auth()->user();
            return $this->sendResponse(new getResource($auth), 'User profile retrieved successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function update(UpdateRequest $request)
    {
        $validated = $request->validated();

        try {
            $auth = auth()->user();
            $auth->name = $validated['name'];
            $auth->email = $validated['email'];
            $auth->province = $validated['province'] ?? $auth->province;
            $auth->city = $validated['city'] ?? $auth->city;
            $auth->address = $validated['address'] ?? $auth->address;
            $auth->save();

            return $this->sendResponse(new UpdateResource($auth), 'User profile updated successfully.');
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

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
