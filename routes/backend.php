<?php



use App\Http\Controllers\Web\Backend\CategoryController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\DynamicPageController;
use App\Http\Controllers\Web\Backend\PermissionController;
use App\Http\Controllers\Web\Backend\PostController;
use App\Http\Controllers\Web\Backend\ProfileController;
use App\Http\Controllers\Web\Backend\RoleController;
use App\Http\Controllers\Web\Backend\SettingController;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\UserRoleManagementController;

//! dashboard
Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');

Route::controller(PostController::class)->group(function () {
    Route::get('/posts', 'index')->name('post.index');
    Route::get('/posts/{id}', 'show')->name('post.show');
    Route::post('/posts/{id}/update', 'update')->name('post.update');
    Route::post('/posts/{id}/approve', 'approve')->name('post.approve');
    Route::post('/posts/{id}/reject', 'reject')->name('post.reject');
    Route::post('/posts/{id}/featured', 'toggleFeatured')->name('post.featured');
    Route::delete('/posts/{id}/delete', 'destroy')->name('post.delete');
    Route::get('/posts/{id}/reports', 'getPostReports')->name('post.reports');
    Route::post('/posts/{id}/reports/read-all', 'markAllPostReportsRead')->name('post.reports.read-all');
});

//! categories
Route::controller(CategoryController::class)->group(function () {
    Route::get('/category', 'index')->name('category.index');
    Route::post('/category/store', 'store')->name('category.store');
    Route::get('/category/{id}/edit', 'edit')->name('category.edit');
    Route::post('/category/update/{id}', 'update')->name('category.update');
    Route::delete('/category/{id}/delete', 'destroy')->name('category.delete');
});
//! profile
Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile',  'index')->name('profile.edit');
    Route::patch('/profile', 'update')->name('profile.update');
    Route::patch('/password', 'passwordChange')->name('password.change');
    Route::patch('/email-update', 'updateEmail')->name('email.change');
});
//! setting
Route::controller(SettingController::class)->group(function () {
    //! account or system setting
    Route::get('/setting', 'index')->name('setting.index');
    Route::post('/setting', 'store')->name('setting.store');

    //! smtp setting
    Route::get('/smtp-setting', 'smtpIndex')->name('smtp.index');
    Route::post('/smtp-setting', 'smtpStore')->name('smtp.store');
});


//! users
Route::controller(UserController::class)->group(function () {
    Route::get('/user', 'index')->name('user.index');
    Route::post('/user', 'store')->name('user.store');
    Route::get('/user/{id}/show', 'show')->name('user.show');
    Route::get('/user/{id}/edit',  'edit')->name('user.edit');
    Route::put('/user/{id}/update',  'update')->name('user.update');
    Route::delete('/user/{id}/delete',  'delete')->name('user.delete');
    Route::get('/user/{id}/reports', 'getReports')->name('user.reports');
    Route::post('/user/{id}/suspend', 'suspend')->name('user.suspend');
    Route::post('/user/{id}/unsuspend', 'unsuspend')->name('user.unsuspend');
    Route::post('/user/{id}/approve-delete', 'approveDelete')->name('user.approve-delete');
    Route::post('/user/{id}/reject-delete', 'rejectDelete')->name('user.reject-delete');
    Route::post('/user/send-email', 'sendCustomEmail')->name('user.send-email');
    Route::post('/notifications/{id}/read', 'markNotificationRead')->name('notifications.read');
    Route::post('/notifications/read-all', 'markAllNotificationsRead')->name('notifications.read-all');
    Route::post('/notifications/clear', 'clearNotifications')->name('notifications.clear');
    Route::post('/reports/{id}/read', 'markReportRead')->name('reports.read');
    Route::post('/user/{id}/reports/read-all', 'markAllReportsRead')->name('user.reports.read-all');
    Route::delete('/reports/{id}', 'deleteReport')->name('reports.delete');
    Route::post('/reports/bulk-delete', 'bulkDeleteReports')->name('reports.bulk-delete');
});


//! Route for role management
Route::controller(RoleController::class)->group(function () {
    Route::get('/roles', 'index')->name('roles.index');
    Route::get('/roles/add', 'create')->name('roles.add');
    Route::post('/role/store', 'store')->name('role.store');
    Route::get('/role/edit/{id}', 'edit')->name('role.edit');
    Route::post('/role/update/{id}', 'update')->name('role.update');
    Route::delete('/role/delete/{id}', 'destroy')->name('role.delete');
});

//! Route for permission management
Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');  // Get permissions


//! Route for user role management
Route::prefix('user')->controller(UserRoleManagementController::class)->group(function () {
    Route::get('/roles', 'index')->name('user.roles.index');
    Route::post('/{id}/attach-role', 'attachRole')->name('user.attach.role');
    Route::post('/{id}/detach-role', 'detachRole')->name('user.detach.role');
});


//! Route for dynamic page
Route::prefix('/dynamic-pages')->controller(DynamicPageController::class)->group(function () {
    Route::get('/', 'index')->name('dynamic-pages.index');
    Route::get('/create', 'create')->name('dynamic-pages.create');
    Route::post('/', 'store')->name('dynamic-pages.store');
    Route::get('/{id}/show', 'show')->name('dynamic-pages.show');
    Route::get('/{id}/edit', 'edit')->name('dynamic-pages.edit');
    Route::post('/{id}','update')->name('dynamic-pages.update');
    Route::delete('/{id}','destroy')->name('dynamic-pages.delete');
});

Route::get('/migrate-uploads', function() {
    $src = storage_path('app/public/uploads');
    $dst = public_path('uploads');
    if (file_exists($src)) {
        if (!file_exists($dst)) {
            mkdir($dst, 0777, true);
        }
        $recurseCopy = function($src, $dst) use (&$recurseCopy) {
            $dir = opendir($src);
            @mkdir($dst, 0777, true);
            while(false !== ( $file = readdir($dir)) ) {
                if (( $file != '.' ) && ( $file != '..' )) {
                    if ( is_dir($src . '/' . $file) ) {
                        $recurseCopy($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        };
        $recurseCopy($src, $dst);
        return "Uploads successfully copied to public/uploads!";
    }
    return "No source uploads folder found in storage/app/public/uploads.";
})->name('admin.migrate-uploads');
