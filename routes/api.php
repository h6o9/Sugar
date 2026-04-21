<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\AddToCartController;
use App\Http\Controllers\Api\PlaceOrderController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductDetailsController;

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

// get reward history
Route::get('/reward-history', [\App\Http\Controllers\Api\RewardHistoryController::class, 'index']);

});
