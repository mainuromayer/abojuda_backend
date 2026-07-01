<?php

use App\Http\Controllers\API\Auth\User\ProfileController;
use App\Http\Controllers\API\Auth\User\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\User\AuthenticationController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\PostController;

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


//! Route for categories
Route::prefix('v1')->controller(PostController::class)->group(function () {
    Route::get('/categories', 'categories');
});


// Route for user social auth
Route::post('v1/social-login', [SocialAuthController::class, 'SocialLogin']);


Route::group(['middleware' => ['jwt.verify']], function () {
    /**
     * Profile
     */
    Route::prefix('v1/auth')->controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index');
        Route::put('/profile', 'update');
        Route::post('/logout', 'logout');
    });

    /**
     * Posts / Listings
     */
    Route::prefix('v1/auth')->controller(PostController::class)->group(function () {
        Route::post('/post/draft', 'saveDraft');
        Route::get('/post/draft', 'getDraft');
        Route::post('/post/create', 'create');
    });

});
