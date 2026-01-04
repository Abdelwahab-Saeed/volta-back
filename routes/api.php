<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    Route::middleware('admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });

    // USERS
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

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
    Route::delete('/cart/{cartItem}', [App\Http\Controllers\Api\CartController::class, 'destroy']);

    // ADDRESSES
    Route::apiResource('addresses', App\Http\Controllers\Api\AddressController::class);

    // COUPONS
    Route::post('/coupons/apply', [App\Http\Controllers\Api\CouponController::class, 'apply']);
    // Admin routes for coupons could be protected by admin middleware, but for now putting here or under 'admin' group if it existed
    Route::apiResource('coupons', App\Http\Controllers\Api\CouponController::class);

    // CHECKOUT
    Route::post('/checkout', [App\Http\Controllers\Api\CheckoutController::class, 'store']);

    // ORDERS
    Route::get('/orders', [App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('/orders/{order}', [App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel', [App\Http\Controllers\Api\OrderController::class, 'cancel']);

    // ADMIN ORDERS
    Route::middleware('admin')->group(function () {
        Route::get('/admin/orders', [App\Http\Controllers\Api\OrderController::class, 'all']);
    });

});
