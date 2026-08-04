<?php

use App\Http\Controllers\API\Auth\User\AuthenticationController;
use App\Http\Controllers\API\Auth\User\ProfileController;
use App\Http\Controllers\API\Auth\User\SocialAuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\FirebaseTokenController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\SupportController;
use App\Http\Controllers\API\WishController;
use App\Http\Controllers\API\DynamicPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//! Route for user
Route::prefix('v1')->controller(AuthenticationController::class)->group(function () {
    Route::post('/sign-up', 'signUp');
    Route::post('/verify/otp', 'verifyOtp');
    Route::post('/login', 'login');
    Route::post('/guest', 'guestLogin');
    Route::post('/forgot-password', 'forgotPassword');
    Route::post('/reset-password', 'resetPassword');
    Route::post('/resend-otp', 'resendOtp');
});

//! Route for locations
Route::prefix('v1')->controller(LocationController::class)->group(function () {
    Route::get('/locations/provinces', 'provinces');
    Route::get('/locations/provinces/{province}/cities', 'cities');
    Route::post('/locations/select', 'selectLocation');
});


//! Route for categories and posts
Route::prefix('v1')->controller(PostController::class)->group(function () {
    Route::get('/categories', 'categories');
    Route::get('/posts', 'index');
    Route::get('/post/details', 'show');
    Route::get('/posts/recent', 'recent');
    Route::get('/posts/featured', 'featured');
    Route::get('/post/related', 'related');
});


// Route for user social auth
Route::post('v1/social-login', [SocialAuthController::class, 'SocialLogin']);

// Route for contact support
Route::post('v1/contact-support', [SupportController::class, 'contactSupport']);

//! Route for dynamic pages
Route::prefix('v1')->controller(DynamicPageController::class)->group(function () {
    Route::get('/pages', 'index');
    Route::get('/page', 'show');
});

/**
 * Firebase Token Module
 */
Route::prefix('v1/firebase/token')->controller(FirebaseTokenController::class)->group(function () {
    Route::post('/add', 'store');
    Route::post('/get', 'getToken');
    Route::post('/delete', 'deleteToken');
});

Route::group(['middleware' => ['jwt.verify']], function () {
    /**
     * Guest-accessible authenticated routes
     */
    Route::prefix('v1/auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'index']);
        Route::post('/logout', [ProfileController::class, 'logout']);

        Route::get('/wishes', [WishController::class, 'index']);
        Route::post('/wishes', [WishController::class, 'toggle']);
    });

    /**
     * Restricted authenticated routes (blocked for guests)
     */
    Route::group(['middleware' => ['block.guest']], function () {
        Route::prefix('v1/auth')->group(function () {
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::post('/change-password', [ProfileController::class, 'changePassword']);
            Route::post('/block', [ProfileController::class, 'blockUser']);
            Route::post('/unblock', [ProfileController::class, 'unblockUser']);
            Route::get('/blocked-users', [ProfileController::class, 'blockedUsersList']);
            Route::post('/delete-account', [ProfileController::class, 'deleteAccount']);
            Route::get('/notification-settings', [ProfileController::class, 'getNotificationSettings']);
            Route::put('/notification-settings', [ProfileController::class, 'updateNotificationSettings']);

            Route::post('/post/draft', [PostController::class, 'saveDraft']);
            Route::get('/post/draft', [PostController::class, 'getDraft']);
            Route::post('/post/create', [PostController::class, 'store']);
            Route::post('/post/update', [PostController::class, 'update']); // Edit Listing Route
            Route::get('/posts', [PostController::class, 'userPosts']);
            Route::post('/posts', [PostController::class, 'deletePost']);

            Route::post('/report/user', [ReportController::class, 'reportUser']);
            Route::post('/report/post', [ReportController::class, 'reportPost']);

            // Chat/Messaging Routes
            Route::get('/conversations', [ChatController::class, 'index']);
            Route::get('/conversations/messages', [ChatController::class, 'messages']);
            Route::post('/conversations/send', [ChatController::class, 'sendMessage']);
            Route::post('/conversations/read', [ChatController::class, 'markAsRead']);
        });
    });
});

Route::get('v1/test-geolocation', function (\Illuminate\Http\Request $request) {
    $address = $request->query('address', 'Brandon, Manitoba');
    $geoLocation = app(\App\Actions\GeoLocation::class);
    
    $client = new \GuzzleHttp\Client();
    $apiKey = env('GOOGLE_MAPS_API_KEY');
    
    try {
        $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'query' => [
                'address' => $address,
                'key' => $apiKey,
            ],
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        return response()->json([
            'address' => $address,
            'api_key_used' => substr($apiKey, 0, 8) . '...',
            'google_response' => $data,
            'resolved_coordinates' => $geoLocation->getCoordinates($address)
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});
