<?php

use App\Http\Controllers\Admin\AddonController as AdminAddonController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController as AdminDeliveryZoneController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Api\Admin\OrderInboxController as ApiAdminOrderInboxController;
use App\Http\Controllers\Api\CartController as ApiCartController;
use App\Http\Controllers\Api\CheckoutPreviewController as ApiCheckoutPreviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/{slug}', [MenuController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('menu.show');

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/items', [CartController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('cart.items.store');
Route::patch('/cart/items/{key}', [CartController::class, 'update'])
    ->middleware('throttle:60,1')
    ->name('cart.items.update');
Route::delete('/cart/items/{key}', [CartController::class, 'destroy'])
    ->middleware('throttle:60,1')
    ->name('cart.items.destroy');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');
Route::get('/orders/confirmation/{reference}', [CheckoutController::class, 'confirmation'])
    ->name('orders.confirmation');

Route::get('/orders/track', [OrderTrackingController::class, 'create'])->name('orders.track');
Route::post('/orders/track', [OrderTrackingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('orders.track.store');
Route::get('/orders/track/{reference}', [OrderTrackingController::class, 'show'])
    ->name('orders.track.show');
Route::get('/orders/track/{reference}/status', [OrderTrackingController::class, 'status'])
    ->middleware('throttle:60,1')
    ->name('orders.track.status');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/delivery-policy', [PageController::class, 'deliveryPolicy'])->name('delivery-policy');

/*
| Session-backed JSON contracts (web middleware for cookies/CSRF).
| Public catalogue JSON remains in routes/api.php.
*/
Route::prefix('api')->name('api.')->middleware('throttle:60,1')->group(function () {
    Route::get('/cart', [ApiCartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [ApiCartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{key}', [ApiCartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{key}', [ApiCartController::class, 'destroy'])->name('cart.items.destroy');
    Route::get('/checkout/preview', ApiCheckoutPreviewController::class)->name('checkout.preview');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::post('/logout', [AdminLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'admin.access'])->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::middleware('menu.manage')->group(function () {
            Route::resource('categories', AdminCategoryController::class)->except(['show']);
            Route::resource('products', AdminProductController::class)->except(['show']);
            Route::resource('addons', AdminAddonController::class)->except(['show']);
        });

        Route::middleware('orders.manage')->group(function () {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/transition', [AdminOrderController::class, 'transition'])
                ->middleware('throttle:60,1')
                ->name('orders.transition');
            Route::get('/orders/{order}/ticket', [AdminOrderController::class, 'ticket'])->name('orders.ticket');
            Route::get('/api/orders/inbox', ApiAdminOrderInboxController::class)
                ->middleware('throttle:60,1')
                ->name('api.orders.inbox');
        });

        Route::post('/orders/{order}/collect', [AdminOrderController::class, 'collect'])
            ->middleware(['cash.collect', 'throttle:30,1'])
            ->name('orders.collect');

        Route::middleware('settings.manage')->group(function () {
            Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
            Route::put('/settings/hours', [AdminSettingsController::class, 'updateHours'])->name('settings.hours');
            Route::resource('delivery-zones', AdminDeliveryZoneController::class)->except(['show']);
        });

        Route::get('/reports', AdminReportController::class)
            ->middleware('reports.view')
            ->name('reports.index');
    });
});
