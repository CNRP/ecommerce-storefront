<?php

use App\Http\Controllers\Api\CheckoutController as ApiCheckoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Frontend\AccountController;
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

Route::prefix('api/cart')->name('api.cart.')->group(function () {
    Route::get('/data', [CartController::class, 'get'])->name('get');  // Changed to /data
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/{key}', [CartController::class, 'update'])->name('update');
    Route::delete('/{key}', [CartController::class, 'remove'])->name('remove');
});
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password reset routes
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated routes - using custom check instead of middleware
Route::group([], function () {
    // Logout (accessible to authenticated users)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email verification routes (optional)
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

    // Account/Profile routes - authentication checked in controller
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/account/edit', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
});

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
