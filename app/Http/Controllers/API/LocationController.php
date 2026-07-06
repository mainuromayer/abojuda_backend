<?php

namespace App\Http\Controllers\API;

use App\Actions\GeoLocation;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Location\SelectLocationRequest;
use App\Http\Resources\Api\V1\Location\LocationSelectResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LocationController extends Controller
{
    use ApiResponse;

    protected $locations = [
        'Ontario' => ['Toronto', 'Ottawa', 'Mississauga', 'Brampton', 'Hamilton', 'London'],
        'Quebec' => ['Montreal', 'Quebec City', 'Laval', 'Gatineau', 'Longueuil', 'Sherbrooke'],
        'British Columbia' => ['Vancouver', 'Victoria', 'Surrey', 'Burnaby', 'Richmond', 'Coquitlam'],
        'Alberta' => ['Calgary', 'Edmonton', 'Red Deer', 'Lethbridge', 'Wood Buffalo', 'St. Albert'],
        'Manitoba' => ['Winnipeg', 'Brandon', 'Steinbach', 'Thompson', 'Portage la Prairie', 'The Pas'],
        'Saskatchewan' => ['Saskatoon', 'Regina', 'Prince Albert', 'Moose Jaw', 'Swift Current'],
        'Nova Scotia' => ['Halifax', 'Sydney', 'Dartmouth', 'Truro', 'New Glasgow'],
        'New Brunswick' => ['Moncton', 'Saint John', 'Fredericton', 'Dieppe', 'Miramichi'],
        'Newfoundland and Labrador' => ["St. John's", 'Mount Pearl', 'Corner Brook', 'Grand Falls-Windsor'],
        'Prince Edward Island' => ['Charlottetown', 'Summerside', 'Stratford', 'Cornwall'],
        'Yukon' => ['Whitehorse'],
        'Northwest Territories' => ['Yellowknife'],
        'Nunavut' => ['Iqaluit'],
    ];

    public function provinces()
    {
        $provinceList = array_keys($this->locations);
        return $this->sendResponse($provinceList, 'Provinces retrieved successfully.', 200);
    }

    public function cities($province)
    {
        $province = urldecode($province);

        if (!array_key_exists($province, $this->locations)) {
            return $this->sendError('Province not found.', [], 404);
        }

        return $this->sendResponse($this->locations[$province], 'Cities retrieved successfully.', 200);
    }

    public function selectLocation(SelectLocationRequest $request)
    {
        $province = $request->province;
        $city = $request->city;

        if (!array_key_exists($province, $this->locations) || !in_array($city, $this->locations[$province])) {
            return $this->sendError('Invalid province or city selection.', [], 400);
        }

        $user = null;

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        }

        if (!$user && $request->guest_token) {
            try {
                $user = JWTAuth::setToken($request->guest_token)->authenticate();
            } catch (Exception $e) {
                return $this->sendError('Invalid guest token.', [], 401);
            }
        }

        if (!$user) {
            return $this->sendError('Unauthorized or invalid token. Please login or provide a valid guest token.', [], 401);
        }

        if ($user->role === Role::GUEST && $user->guest_expires_at && now()->timestamp > $user->guest_expires_at) {
            return $this->sendError('Guest session has expired. Please create a new guest session.', [], 401);
        }

        $user->province = $province;
        $user->city = $city;

        // Resolve coordinates based on province and city
        $address = implode(', ', array_filter([$city, $province]));
        $geoLocation = app(GeoLocation::class);
        $coords = $geoLocation->getCoordinates($address);
        $user->latitude = $coords['latitude'] ?? null;
        $user->longitude = $coords['longitude'] ?? null;

        $user->save();

        return $this->sendResponse(new LocationSelectResource($user), 'Location updated successfully.', 200);
    }
}
