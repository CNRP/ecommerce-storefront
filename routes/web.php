<?php

use App\Http\Controllers\Api\CheckoutController as ApiCheckoutController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Webhooks\StripeController;
use Illuminate\Support\Facades\Route;

// Frontend routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Categories
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::get('/confirmation/{orderNumber}', [CheckoutController::class, 'confirmation'])->name('confirmation');
});

// Order routes
Route::prefix('orders')->name('orders.')->group(function () {
    // Guest order tracking
    Route::get('/track', [OrderController::class, 'track'])->name('track');
    Route::post('/track', [OrderController::class, 'track'])->name('track.submit');

    // Customer order history (requires auth)
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
    });

    // Order details (works for both guests with token and authenticated users)
    Route::get('/{orderNumber}', [OrderController::class, 'show'])->name('show');
    Route::post('/{orderNumber}/cancel', [OrderController::class, 'cancel'])->name('cancel');
});

// API routes for AJAX checkout
Route::prefix('api/checkout')->name('api.checkout.')->group(function () {
    Route::post('/initialize', [ApiCheckoutController::class, 'initialize'])->name('initialize');
    Route::post('/complete', [ApiCheckoutController::class, 'complete'])->name('complete');
    Route::post('/error', [ApiCheckoutController::class, 'error'])->name('error');
    Route::get('/summary', [ApiCheckoutController::class, 'summary'])->name('summary');
});

// Webhook routes (no CSRF protection needed)
Route::post('/webhooks/stripe', [StripeController::class, 'handle'])
    ->name('webhooks.stripe')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
