<?php
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\BannerController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Auth Routes
Route::get('admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login.post');

// Admin Password Reset Routes
Route::get('admin/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('admin.password.request');
Route::post('admin/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('admin.password.email');
Route::get('admin/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('admin.password.reset');
Route::post('admin/reset-password', [AuthController::class, 'resetPassword'])->name('admin.password.update');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Basic Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Resource CRUDs
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
    Route::resource('coupons', CouponController::class);
    Route::resource('banners', BannerController::class);
    
    // Bundle Offers
    Route::get('products/{product}/offers', [App\Http\Controllers\Admin\ProductBundleOfferController::class, 'index'])->name('products.offers.index');
    Route::post('products/{product}/offers', [App\Http\Controllers\Admin\ProductBundleOfferController::class, 'store'])->name('products.offers.store');
    Route::get('products/{product}/offers/{offer}/edit', [App\Http\Controllers\Admin\ProductBundleOfferController::class, 'edit'])->name('products.offers.edit');
    Route::put('products/{product}/offers/{offer}', [App\Http\Controllers\Admin\ProductBundleOfferController::class, 'update'])->name('products.offers.update');
    Route::delete('products/{product}/offers/{offer}', [App\Http\Controllers\Admin\ProductBundleOfferController::class, 'destroy'])->name('products.offers.destroy');
});
