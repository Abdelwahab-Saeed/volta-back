<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// SETTINGS
Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index']);

// CHECKOUT
Route::post('/checkout', [App\Http\Controllers\Api\CheckoutController::class, 'store']);
// Offers are bought directly, never through the cart
Route::post('/checkout/offer', [App\Http\Controllers\Api\OfferCheckoutController::class, 'store']);

// TRACKING
Route::post('/track/pageview', [App\Http\Controllers\Api\TrackingController::class, 'pageView']);

// CATEGORIES
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// PRODUCTS
Route::get('/products/best-selling', [ProductController::class, 'bestSelling']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// BANNERS
Route::get('/banners', [App\Http\Controllers\Api\BannerController::class, 'index']);

// OFFERS
Route::get('/offers', [App\Http\Controllers\Api\OfferController::class, 'index']);
Route::get('/offers/all', [App\Http\Controllers\Api\OfferController::class, 'all']);
Route::get('/offers/{id}', [App\Http\Controllers\Api\OfferController::class, 'show']);
Route::get('/offers/{id}/quote', [App\Http\Controllers\Api\OfferController::class, 'quote']);

// COMPANY PROFILE (home page: partners & clients, certificates, team)
Route::get('/partners', [App\Http\Controllers\Api\CompanyProfileController::class, 'partners']);
Route::get('/certificates', [App\Http\Controllers\Api\CompanyProfileController::class, 'certificates']);
Route::get('/team', [App\Http\Controllers\Api\CompanyProfileController::class, 'team']);

// POSTS (BLOG)
Route::get('/posts', [App\Http\Controllers\Api\PostController::class, 'index']);
Route::get('/posts/{post}', [App\Http\Controllers\Api\PostController::class, 'show']);

// PASSWORD RESET
Route::middleware('throttle:password-reset')->group(function () {
    Route::post('/password/forgot', [App\Http\Controllers\Api\PasswordResetController::class, 'forgotPassword']);
    Route::post('/password/reset', [App\Http\Controllers\Api\PasswordResetController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    

    // ADMIN
    Route::middleware('admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // POSTS (ADMIN)
        Route::post('/posts', [App\Http\Controllers\Api\PostController::class, 'store']);
        Route::put('/posts/{post}', [App\Http\Controllers\Api\PostController::class, 'update']);
        Route::delete('/posts/{post}', [App\Http\Controllers\Api\PostController::class, 'destroy']);
    });



    // ADMIN
    Route::middleware('admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    // CART
    Route::get('/cart', [App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart', [App\Http\Controllers\Api\CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [App\Http\Controllers\Api\CartController::class, 'update']);
    Route::post('/cart/clear', [App\Http\Controllers\Api\CartController::class, 'clear']);
    // The guest cart (kept in the browser) joins the account cart right after login
    Route::post('/cart/merge', [App\Http\Controllers\Api\CartController::class, 'merge']);
    Route::delete('/cart/{cartItem}', [App\Http\Controllers\Api\CartController::class, 'destroy']);

    // ADDRESSES
    Route::get('addresses/user', [App\Http\Controllers\Api\AddressController::class, 'myAddresses']);
    Route::apiResource('addresses', App\Http\Controllers\Api\AddressController::class);

    // COUPONS
    Route::post('/coupons/apply', [App\Http\Controllers\Api\CouponController::class, 'apply']);

    // ORDERS
    Route::get('/orders', [App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('/orders/{order}', [App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [App\Http\Controllers\Api\OrderController::class, 'cancel']);

    // WISHLIST
    Route::get('/wishlist', [App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [App\Http\Controllers\Api\WishlistController::class, 'toggle']);

    // COMPARISON
    Route::get('/comparison', [App\Http\Controllers\Api\ComparisonController::class, 'index']);
    Route::post('/comparison', [App\Http\Controllers\Api\ComparisonController::class, 'store']);
    Route::delete('/comparison/{product}', [App\Http\Controllers\Api\ComparisonController::class, 'destroy']);

    // ADMIN ORDERS & COUPONS (customers only apply coupons and cancel their own pending orders)
    Route::middleware('admin')->group(function () {
        Route::get('/admin/orders', [App\Http\Controllers\Api\OrderController::class, 'all']);
        Route::patch('/orders/{order}/status', [App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
        Route::apiResource('coupons', App\Http\Controllers\Api\CouponController::class);
    });

});
