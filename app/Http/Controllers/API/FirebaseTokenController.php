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
            $user = FirebaseToken::where('user_id', $request->user_id)->where('device_id', $request->device_id)->first();
            if ($user) {
                $user->delete();
            }

            $data = new FirebaseToken();
            $data->user_id = $request->user_id;
            $data->token = $request->token;
            $data->device_id = $request->device_id;
            $data->is_active = $request->is_active ?? true;
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
        $user = FirebaseToken::where('user_id', $request->user_id)->where('device_id', $request->device_id)->first();
        if ($user) {
            $user->delete();
            return $this->sendResponse([], 'Token deleted successfully');
        } else {
            return $this->sendError('No records found', [], 404);
        }
    }
}
