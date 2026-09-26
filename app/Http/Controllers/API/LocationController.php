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
use App\Helpers\helpers;
use App\Helpers\translateHelper;

class LocationController extends Controller
{
    use ApiResponse;

    protected $locations;

    public function __construct()
    {
        $this->locations = config('locations');
    }

    public function provinces()
    {
        $lang = request()->get('lang') ?? request()->header('lang') ?? (substr(request()->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($lang, ['en', 'ar'])) {
            $lang = 'ar';
        }

        $provinceList = array_keys($this->locations);

        if ($lang === 'ar') {
            $provinceList = array_map(function ($province) use ($lang) {
                $key = 'location.province.' . str_replace(' ', '_', $province);

                return translateHelper::translateCached(
                    $key,
                    $province,
                    $lang
                );
            }, $provinceList);
        }

        $message = translateHelper::translateCached(
            'location.provinces.success',
            'Provinces retrieved successfully.',
            $lang
        );

        return $this->sendResponse(
            $provinceList,
            $message,
            200
        );
    }

    public function cities($province)
    {
        $province = urldecode($province);

        $lang = request()->get('lang') ?? request()->header('lang') ?? (substr(request()->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($lang, ['en', 'ar'])) {
            $lang = 'ar';
        }

        $matchedProvince = null;

        foreach (array_keys($this->locations) as $englishProvince) {
            $key = 'location.province.' . str_replace(' ', '_', $englishProvince);

            $arabicProvince = translateHelper::translateCached(
                $key,
                $englishProvince,
                'ar'
            );

            if (
                strcasecmp($province, $englishProvince) === 0 ||
                mb_strtolower($province, 'UTF-8') === mb_strtolower($arabicProvince, 'UTF-8')
            ) {
                $matchedProvince = $englishProvince;
                break;
            }
        }

        if (!$matchedProvince) {
            $message = translateHelper::translateCached(
                'location.province.not_found',
                'Province not found.',
                $lang
            );

            return $this->sendError($message, [], 404);
        }

        $cities = $this->locations[$matchedProvince];

        $cities = array_map(function ($city) use ($matchedProvince, $lang) {

            if ($lang === 'en') {
                return $city;
            }

            $key = 'location.city.' .
                str_replace(' ', '_', $matchedProvince) .
                '.' .
                str_replace(' ', '_', $city);

            return translateHelper::translateCached(
                $key,
                $city,
                $lang
            );

        }, $cities);

        $message = translateHelper::translateCached(
            'location.cities.success',
            'Cities retrieved successfully.',
            $lang
        );

        return $this->sendResponse(
            $cities,
            $message,
            200
        );
    }

    public function selectLocation(SelectLocationRequest $request)
    {
        $locale = $request->get('lang') ?? $request->header('lang') ?? (substr($request->header('Accept-Language', ''), 0, 2) ?: app()->getLocale());
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'ar';
        }

        $province = trim($request->province);
        $city = trim($request->city);

        $matchedProvince = null;
        $matchedCity = null;

        foreach ($this->locations as $englishProvince => $cities) {

            $provinceKey = 'location.province.' .
                str_replace(' ', '_', $englishProvince);

            $arabicProvince = translateHelper::translateCached(
                $provinceKey,
                $englishProvince,
                'ar'
            );

            if (
                strcasecmp($province, $englishProvince) === 0 ||
                mb_strtolower($province, 'UTF-8') === mb_strtolower($arabicProvince, 'UTF-8')
            ) {
                $matchedProvince = $englishProvince;

                foreach ($cities as $englishCity) {

                    $cityKey = 'location.city.' .
                        str_replace(' ', '_', $englishProvince) .
                        '.' .
                        str_replace(' ', '_', $englishCity);

                    $arabicCity = translateHelper::translateCached(
                        $cityKey,
                        $englishCity,
                        'ar'
                    );

                    if (
                        strcasecmp($city, $englishCity) === 0 ||
                        mb_strtolower($city, 'UTF-8') === mb_strtolower($arabicCity, 'UTF-8')
                    ) {
                        $matchedCity = $englishCity;
                        break;
                    }
                }

                break;
            }
        }

        if (!$matchedProvince || !$matchedCity) {

            $message = translateHelper::translateCached(
                'location.select.invalid',
                'Invalid province or city selection.',
                $locale
            );

            return $this->sendError(
                $message,
                [],
                400
            );
        }

        $user = null;

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        }

        if (!$user && $request->guest_token) {
            try {
                $user = JWTAuth::setToken(
                    $request->guest_token
                )->authenticate();
            } catch (Exception $e) {

                $message = translateHelper::translateCached(
                    'location.select.invalid_guest_token',
                    'Invalid guest token.',
                    $locale
                );

                return $this->sendError(
                    $message,
                    [],
                    401
                );
            }
        }

        if (!$user) {

            $message = translateHelper::translateCached(
                'location.select.unauthorized',
                'Unauthorized or invalid token. Please login or provide a valid guest token.',
                $locale
            );

            return $this->sendError(
                $message,
                [],
                401
            );
        }

        if (
            $user->role === Role::GUEST &&
            $user->guest_expires_at &&
            now()->timestamp > $user->guest_expires_at
        ) {

            $message = translateHelper::translateCached(
                'location.select.guest_expired',
                'Guest session has expired. Please create a new guest session.',
                $locale
            );

            return $this->sendError(
                $message,
                [],
                401
            );
        }

        $provinceKey = 'location.province.' . str_replace(' ', '_', $matchedProvince);
        $arabicProvince = translateHelper::translateCached($provinceKey, $matchedProvince, 'ar');

        $cityKey = 'location.city.' . str_replace(' ', '_', $matchedProvince) . '.' . str_replace(' ', '_', $matchedCity);
        $arabicCity = translateHelper::translateCached($cityKey, $matchedCity, 'ar');

        $englishAddress = implode(', ', array_filter([$matchedCity, $matchedProvince, 'Canada']));
        $arabicAddress = implode('، ', array_filter([$arabicCity, $arabicProvince, 'كندا']));

        $user->setTranslation('province', 'en', $matchedProvince);
        $user->setTranslation('province', 'ar', $arabicProvince);
        $user->setTranslation('city', 'en', $matchedCity);
        $user->setTranslation('city', 'ar', $arabicCity);
        $user->setTranslation('address', 'en', $englishAddress);
        $user->setTranslation('address', 'ar', $arabicAddress);

        $geoLocation = app(GeoLocation::class);

        $coords = $geoLocation->getCoordinates($englishAddress);

        $user->latitude = $coords['latitude'] ?? null;
        $user->longitude = $coords['longitude'] ?? null;

        $user->save();

        $message = translateHelper::translateCached(
            'location.select.success',
            'Location updated successfully.',
            $locale
        );

        return $this->sendResponse(
            new LocationSelectResource($user),
            $message,
            200
        );
    }
}