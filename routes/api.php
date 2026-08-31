<?php

use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\AddToCartController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PlaceOrderController;
use App\Http\Controllers\Api\ProductDetailsController;
use App\Http\Controllers\Api\StorefrontApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */


// Route::group(['namespace' => 'Api'], function () {
//     Route::post('register', 'AuthController@register');
//     Route::post('login', 'AuthController@login');
//     Route::get('notification', 'AuthController@notification');
//     Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//         return $request->user();
//     });

// });

Route::post('/register-user', [AuthController::class, 'register']);
Route::post('/register-verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/user-social-login', [AuthController::class, 'socialLogin']);
Route::post('/place-order', [PlaceOrderController::class, 'placeOrder']);
Route::post('/login-user', [AuthController::class, 'Login']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/user-forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/user-verify-forget-password', [AuthController::class, 'verifyForgetOtp']);
Route::post('/user-reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->group(function () {
Route::get('/user-get-profile', [AuthController::class, 'getProfile']);
	Route::post('/user-update-profile', [AuthController::class, 'updateProfile']);
		Route::post('/user-change-password', [AuthController::class, 'ChangePassword']);
	Route::post('/verify-update-profile-otp', [AuthController::class, 'verifyUpdateprofileOtp']);
	Route::get('/user-login-info', [AuthController::class, 'getLoggedInUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
	Route::get('/home-products', [HomeController::class, 'homeProducts']);
	Route::get('/menu-items', [HomeController::class, 'Menueitems']);
	Route::get('/toppings', [HomeController::class, 'toppings']);
	Route::get('/my-orders-status', [OrderController::class, 'myOrders']);

Route::get('/product-details/{id}', [ProductDetailsController::class, 'getProductDetails']);
Route::post('/product-add-to-cart', [AddToCartController::class, 'addToCart']);
Route::get('/get-user-cart-items', [AddToCartController::class, 'getUserCartItems']);
Route::post('/cart-update-quantity/{id}', [AddToCartController::class, 'updateCartItemQuantity']);
Route::post('/delete-cart-item/{id}', [AddToCartController::class, 'deleteCartItem']);
Route::post('/continue-to-payments', [AddToCartController::class, 'proceedToPayment']);
Route::post('/place-order', [PlaceOrderController::class, 'placeOrder']);
Route::get('/place-order/{orderId}', [PlaceOrderController::class, 'getConfirmation']);
Route::get('/order-confirmation/{orderId}', [PlaceOrderController::class, 'getConfirmation']);
Route::get('/get-branch-info', [AddToCartController::class, 'getBranchInfo']);

// filter data 

Route::get('/filter-data', [FilterController::class, 'filterData']);
Route::get('/terms-and-conditions', [PageController::class, 'termsAndConditions']);
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy']);
Route::get('/faq', [PageController::class, 'faq']);
Route::get('/gallery', [PageController::class, 'getGalleryImages']);
Route::get('/get-user-reward-amount', [PageController::class, 'getUserRewardAmount']);

//NOTIFICATION ROUTES
// Notifications
Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);
Route::get('/notification/{id}', [NotificationController::class, 'showNotification']);
Route::post('/clearnotification', [NotificationController::class, 'clearAll']);
Route::post('/notifications-seen', [NotificationController::class, 'seenNotification'])
    ->name('notifications.seen');

	// 
	Route::get('/referral/validate/{code}', [ReferralController::class, 'validateReferralCode']);

// Register new user with referral code
Route::post('/register-with-referral', [ReferralController::class, 'registerWithReferral']);
 Route::post('/referral/generate', [ReferralController::class, 'generateLink']);
    
    // Get my referral link and points
    Route::get('/referral/my-link', [ReferralController::class, 'getMyLink']);
    
    // Get complete statistics
    Route::get('/referral/stats', [ReferralController::class, 'getStats']);
    
    // Use/redeem points
    Route::post('/referral/use-points', [ReferralController::class, 'usePoints']);
// get reward history
Route::get('/reward-history', [\App\Http\Controllers\Api\RewardHistoryController::class, 'index']);
Route::get('/order-state', [StorefrontApiController::class, 'orderState']);
Route::post('/orders/add-items', [StorefrontApiController::class, 'addItems']);
Route::post('/orders/update-item', [StorefrontApiController::class, 'updateItem']);
Route::get('/orders/{orderId}/items/{itemId}/options', [StorefrontApiController::class, 'itemOptions']);
Route::post('/orders/remove-items', [StorefrontApiController::class, 'removeItems']);
Route::post('/orders/add-from-cart', [StorefrontApiController::class, 'addFromCart']);
Route::post('/orders/{id}/start-add-items', [StorefrontApiController::class, 'startAddItems']);
Route::post('/orders/{id}/cancel-add-items', [StorefrontApiController::class, 'cancelAddItems']);
Route::get('/orders', [StorefrontApiController::class, 'myOrders']);
Route::get('/orders/{id}', [StorefrontApiController::class, 'orderDetail']);
Route::get('/orders/{id}/receipt', [StorefrontApiController::class, 'receipt']);
Route::get('/storefront/cart-context', [StorefrontApiController::class, 'cartContext']);
Route::post('/storefront/cart-context', [StorefrontApiController::class, 'setCartContext']);
Route::post('/storefront/set-wholesale-date', [StorefrontApiController::class, 'setWholesaleDate']);
Route::post('/storefront/save-pickup-time', [StorefrontApiController::class, 'savePickupTime']);
Route::post('/storefront/checkout-preview', [StorefrontApiController::class, 'checkoutPreview']);
Route::post('/storefront/schedule', [StorefrontApiController::class, 'setSchedule']);

});

Route::get('/business-status', [StorefrontApiController::class, 'businessStatus']);
Route::get('/stores', [StorefrontApiController::class, 'stores']);
Route::get('/wholesale-dates', [StorefrontApiController::class, 'wholesaleDates']);
Route::get('/wholesale-menu', [StorefrontApiController::class, 'wholesaleMenu']);
Route::get('/storefront/config', [StorefrontApiController::class, 'config']);
Route::get('/storefront/home-menu', [StorefrontApiController::class, 'homeMenu']);
Route::get('/storefront/menu', [StorefrontApiController::class, 'homeMenu']);
Route::get('/storefront/special-menu', [StorefrontApiController::class, 'specialMenu']);
Route::get('/storefront/wholesale-menu', [StorefrontApiController::class, 'wholesaleMenu']);
Route::get('/storefront/drive-in-menu', [StorefrontApiController::class, 'driveInMenu']);
Route::get('/storefront/pickup-slots', [StorefrontApiController::class, 'pickupSlots']);
Route::get('/time-intervals', [StorefrontApiController::class, 'pickupSlots']);
Route::get('/storefront/navigation', [StorefrontApiController::class, 'navigation']);
Route::get('/storefront/product/{id}', [StorefrontApiController::class, 'productDetail']);
Route::get('/storefront/private-bookings', [StorefrontApiController::class, 'privateBookings']);

// Webview Payment Routes for App
Route::get('/payment/stripe/webview/success', [\App\Http\Controllers\Api\PaymentController::class, 'stripeWebviewSuccess'])->name('api.payment.stripe.webview.success');
Route::get('/payment/stripe/webview/cancel', [\App\Http\Controllers\Api\PaymentController::class, 'stripeWebviewCancel'])->name('api.payment.stripe.webview.cancel');
// checkout_token from place-order (payment first, then order on success)
Route::get('/payment/stripe/webview/{checkout_token}', [\App\Http\Controllers\Api\PaymentController::class, 'stripeWebviewCheckout'])->name('api.payment.stripe.webview.checkout');
