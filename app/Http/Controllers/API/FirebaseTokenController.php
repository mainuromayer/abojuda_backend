<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FirebaseToken\DeleteTokenRequest;
use App\Http\Requests\Api\V1\FirebaseToken\GetTokenRequest;
use App\Http\Requests\Api\V1\FirebaseToken\StoreTokenRequest;
use App\Http\Resources\Api\V1\FirebaseToken\FirebaseResource;
use App\Models\FirebaseToken;
use App\Traits\ApiResponse;
use Exception;

class FirebaseTokenController extends Controller
{
    use ApiResponse;
    /**
     * News Serve For Frontend
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTokenRequest $request)
    {
        try {
            $userId   = $request->user_id ?? (auth('api')->check() ? auth('api')->id() : null);
            $token    = $request->token;
            $deviceId = $request->device_id;

            // Remove any old record associated with this device or token
            FirebaseToken::where('device_id', $deviceId)
                ->orWhere('token', $token)
                ->delete();

            // Create new object and save
            $data = new FirebaseToken();
            $data->user_id   = $userId;
            $data->token     = $token;
            $data->device_id = $deviceId;
            $data->is_active = $request->has('is_active') ? (bool)$request->is_active : true;
            $data->save();

            return $this->sendResponse(new FirebaseResource($data), 'Token saved successfully');
        } catch (Exception $e) {
            return $this->sendError('An error occurred: ' . $e->getMessage(), [],  500);
        }
    }

    /**
     * Get Single Record
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(GetTokenRequest $request)
    {
        $user_id = $request->user_id;
        $device_id = $request->device_id;
        $data = FirebaseToken::where('user_id', $user_id)->where('device_id', $device_id)->first();
        if (!$data) {
            return $this->sendError('No records found', [], 404);
        }
        return $this->sendResponse(new FirebaseResource($data), 'Token fetched successfully');
    }

    /**
     * Delete Token Single Record
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteToken(DeleteTokenRequest $request)
    {
        try {
            $userId   = $request->user_id ?? (auth('api')->check() ? auth('api')->id() : null);
            $deviceId = $request->device_id;

            $query = FirebaseToken::query()->where('device_id', $deviceId);
            if ($userId) {
                $query->where('user_id', $userId);
            }

            $deleted = $query->delete();
            if ($deleted) {
                return $this->sendResponse([], 'Token deleted successfully');
            } else {
                return $this->sendError('No records found', [], 404);
            }
        } catch (Exception $e) {
            return $this->sendError('An error occurred: ' . $e->getMessage(), [], 500);
        }
    }
}
