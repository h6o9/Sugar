<?php

use App\Http\Controllers\Admin\AboutusController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BulkFeatureController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuGalleryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderCompletionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PolicyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesController as AdminSalesController;
use App\Http\Controllers\Admin\SeaMossController;
use App\Http\Controllers\Admin\TermConditionController;
use App\Http\Controllers\Admin\TimeController;
use App\Http\Controllers\Admin\ToppingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Branch\BranchAuthController;
use App\Http\Controllers\Branch\DashBoardController;
use App\Http\Controllers\Branch\OrderController as BranchOrderController;
use App\Http\Controllers\Branch\RegisterController;
use App\Http\Controllers\Branch\SalesController;
use App\Http\Controllers\Branch\ScheduleController;
use App\Http\Controllers\Home\AuthController as HomeAuthController;
use App\Http\Controllers\Home\BranchUpdateController;
use App\Http\Controllers\Home\CartController;
use App\Http\Controllers\Home\CheckoutController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\LoyalityPointsController;
use App\Http\Controllers\Home\LoyaltyPointController;
use App\Http\Controllers\Home\OrderController;
use App\Http\Controllers\Home\SecurityController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\WebController;
use App\Models\Notification;
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

Route::get('/', function () {
    return redirect()->route('index');
});

/*
Admin routes
 * */
Route::get('/admin-login', [AuthController::class, 'getLoginPage'])->name('admin-login');
Route::post('admin/login', [AuthController::class, 'Login']);
Route::get('/admin-forgot-password', [AdminController::class, 'forgetPassword']);
Route::post('/admin-reset-password-link', [AdminController::class, 'adminResetPasswordLink'])->name('admin-reset-password-link');
Route::get('admin/change_password/{id}', [AdminController::class, 'change_password'])->name('admin-change-password');
Route::post('/admin-reset-password', [AdminController::class, 'ResetPassword'])->name('admin-reset-password');
//Web Views
   Route::get('/terms/conditions', [WebController::class, 'termsConditionspage'])->name('terms.conditions');
   Route::get('/privacy/policy', [WebController::class, 'privacyPolicy'])->name('privacy.policy');
   Route::get('/contact-us', [WebController::class, 'contactuspage'])->name('contact.us');
   // social auth routes
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Notifications Web
Route::get('/notifications', [NotificationController::class, 'Webindex'])->name('notifications.index');


Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'getdashboard']);
    Route::get('profile', [AdminController::class, 'getProfile']);
    Route::post('update-profile', [AdminController::class, 'update_profile']);
    Route::get('logout', [AdminController::class, 'logout']);

    // Branch Views
    Route::get('branch', [BranchController::class, 'view'])->name('branches.index');
    Route::get('create-branch', [BranchController::class, 'create'])->name('create-branch');
    Route::post('register-branch', [BranchController::class, 'store'])->name('register-branch');
    Route::get('branch-edit/{id}', [BranchController::class, 'edit'])->name('branch-edit');
    Route::post('branch-update/{id}', [BranchController::class, 'update'])->name('branch-update');
    Route::delete('/branch-delete/{id}', [BranchController::class, 'destroy'])->name('branch-delete');
    // Branch Views End

		// Web Notifications
Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark.read');
Route::get('/notifications/clear-all-notification', [NotificationController::class, 'clearAllNotifications'])->name('notifications.clear');

	// Order Completion Rewards
	Route::get('/completion-order', [OrderCompletionController::class, 'orderCompletion'])->name('order-completion');
	Route::get('/completion-order-edit/{id}', [App\Http\Controllers\Admin\OrderCompletionController::class, 'EditorderCompletion'])->name('edit-order-completion');
	Route::post('/update-completion-order/{id}', [App\Http\Controllers\Admin\OrderCompletionController::class, 'UpdateorderCompletion'])->name('update-order-completion');

	// Referral Reward Settings
	Route::get('/referral-reward-settings', [App\Http\Controllers\Admin\ReferralLinkSettingController::class, 'referralRewardSettings'])->name('referral-reward-settings');
	Route::get('/referral-reward-settings-edit/{id}', [App\Http\Controllers\Admin\ReferralLinkSettingController::class, 'EditreferralRewardSettings'])->name('edit-referral-reward-settings');
	Route::post('/update-referral-reward-settings/{id}', [App\Http\Controllers\Admin\ReferralLinkSettingController::class, 'UpdatereferralRewardSettings'])->name('update-referral-reward-settings');
    // Reward Settings
	Route::get('/reward-settings', [App\Http\Controllers\Admin\RewardSetingsController::class, 'RewardSettings'])->name('reward-settings');
	Route::get('/reward-settings-edit/{id}', [App\Http\Controllers\Admin\RewardSetingsController::class, 'EditRewardSettings'])->name('edit-reward-settings');
	Route::post('/update-reward-settings/{id}', [App\Http\Controllers\Admin\RewardSetingsController::class, 'updateRewardSettings'])->name('update-reward-settings');

	    // bulk feature settings
	Route::get('/bulk-feature-settings', [BulkFeatureController::class, 'index'])->name('bulk-feature.index');
	Route::get('/bulk-feature-settings-edit/{id}', [BulkFeatureController::class, 'edit'])->name('edit-bulk-feature-settings');
	Route::post('/update-bulk-feature-settings/{id}', [BulkFeatureController::class, 'update'])->name('update-bulk-feature-settings');

	//Notification Routes
	    Route::controller(NotificationController::class)->group(function () {
	  Route::get('/notification',  'index')->name('notification.index');

        Route::post('/notification-store',  'store')->name('notification.store');

        Route::delete('/notification-destroy/{id}',  'destroy')->name('notification.destroy');
        Route::delete('/notifications/delete-all', 'deleteAll')->name('notifications.deleteAll');
        Route::get('/get-users-by-type', 'getUsersByType');
    });

	Route::post('/notification/mark-as-read', function (Request $request) {
    Notification::where('id', $request->id)
        ->update(['seenByUser' => 1]);

    return response()->json(['success' => true]);
})->name('notification.read');
    // User Views
    Route::get('user', [UserController::class, 'userView'])->name('users.index');
    Route::get('user/rewards/{id}', [UserController::class, 'rewards'])->name('rewards');
    Route::delete('/user-delete/{id}', [UserController::class, 'destroy'])->name('users-delete');
    // User Views End


    /** resource controller */
    Route::resource('menu', MenuController::class);
    Route::resource('topping', ToppingController::class);
    Route::resource('category', CategoryController::class);
    Route::get('category/toppings/{id}', [CategoryController::class , 'toppings'])->name('category-toppings');
    Route::post('category/toppingAssign', [CategoryController::class , 'toppingStore'])->name('toppingAssign');
    Route::delete('toppingDestroy/{id}', [CategoryController::class , 'toppingDestroy'])->name('toppingDestroy');
    Route::resource('product', ProductController::class);
    Route::get('product/status/{id}', [ProductController::class, 'status'])->name('admin.status');
    Route::resource('gallery', GalleryController::class);
    Route::resource('policy', PolicyController::class);
    Route::resource('terms', TermConditionController::class);
    Route::resource('faq', FaqController::class);
    Route::get('/featured/{id}', [ProductController::class, 'toggleFeatured'])->name('admin.featured');
Route::post('/update-branch-status', [CartController::class, 'updateBranchStatus'])->name('update.branch.status');

    //Time Slots
    Route::get('time-slots', [TimeController::class, 'index'])->name('time-slot.index');
    Route::post('add-slots', [TimeController::class, 'store'])->name('time-slot.store');
    // Orders
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    // Sales
    Route::get('sales', [AdminSalesController::class, 'salesIndex'])->name('sales.index');
    Route::get('dailySales/{id}', [AdminSalesController::class, 'showSales'])->name('admindailySalesDetails');
    Route::get('weeklySales/{id}', [AdminSalesController::class, 'showWeeklySales'])->name('adminweeklySalesDetails');
    Route::get('monthlySales/{id}', [AdminSalesController::class, 'showMonthlySales'])->name('adminmonthlySalesDetails');
    Route::get('yearlySales/{id}', [AdminSalesController::class, 'showYearlySales'])->name('adminyearlySalesDetails');


    // Menu Gallery
    Route::get('m-gallery', [MenuGalleryController::class, 'menuGalleryIndex'])->name('m-gallery.index');
    Route::get('create/menuGallery', [MenuGalleryController::class, 'createmenuGallery'])->name('menugallery.create');
    Route::post('store/menuGallery', [MenuGalleryController::class, 'storeImage'])->name('menugallery.store');
    Route::get('edit/menuGallery/{id}', [MenuGalleryController::class, 'editImage'])->name('menuGallery.edit');
    Route::post('update/menuGallery/{id}', [MenuGalleryController::class, 'updateImage'])->name('menuGallery.update');
    Route::delete('delete/menuGallery/{id}', [MenuGalleryController::class, 'destroy'])->name('menuGallery.destroy');

    // NEW!SEA MOSS
    Route::get('seamoss', [SeaMossController::class, 'seamossIndex'])->name('seamoss.index');
    Route::get('create/seamoss', [SeaMossController::class, 'createSeamoss'])->name('seamoss.create');
    Route::post('store/seamoss', [SeaMossController::class, 'storeSeamoss'])->name('seamoss.store');
    Route::get('edit/seamoss/{id}', [SeaMossController::class, 'editSeamoss'])->name('seasmoss.edit');
    Route::post('update/seamoss/{id}', [SeaMossController::class, 'updateSeamoss'])->name('seamoss.update');
});

//  Branch Auth Routes
Route::get('/branch', [BranchAuthController::class, 'getLoginPage'])->name('branch');
Route::post('branch/login', [BranchAuthController::class, 'Login']);
Route::get('/branch-forgot-password', [BranchAuthController::class, 'forgetBranchPassword'])->name('branch-forgot-password');
Route::post('/branch-reset-password-link', [BranchAuthController::class, 'branchResetPasswordLink']);
Route::get('/change_password/{id}', [BranchAuthController::class, 'change_password']);
Route::post('/branch-reset-password', [BranchAuthController::class, 'ResetPassword']);
Route::get('getBranchRegistor', [RegisterController::class, 'getBranchRegistor'])->name('getBranchRegistor');
Route::post('/branchStore', [RegisterController::class, 'store'])->name('branch.register');
// Auth Routes End

Route::prefix('branch')->middleware('auth:branch')->group(function () {
    Route::get('dashboard', [DashBoardController::class, 'getbranchdashboard']);
    Route::get('/profile', [DashBoardController::class, 'getBranchProfile']);
    Route::get('/logout', [DashBoardController::class, 'branchlogout']);
    Route::post('update-profile', [DashBoardController::class, 'updateBranchProfile']);
    Route::get('schedule', [ScheduleController::class, 'index']);
    Route::post('/save-schedule', [ScheduleController::class, 'saveSchedule'])->name('save.schedule');
    Route::post('/branch-pickUpTime', [ScheduleController::class, 'updatePickupSchedule'])->name('save.pickup.schedule');

    //Branch Orders
    Route::get('/order', [BranchOrderController::class, 'orderIndex']);

    // Sales
    Route::get('sales', [SalesController::class, 'salesIndex']);
    Route::get('dailySales/{id}', [SalesController::class, 'showSales'])->name('dailySalesDetails');
    Route::get('weeklySales/{id}', [SalesController::class, 'showWeeklySales'])->name('weeklySalesDetails');
    Route::get('monthlySales/{id}', [SalesController::class, 'showMonthlySales'])->name('monthlySalesDetails');
    Route::get('yearlySales/{id}', [SalesController::class, 'showYearlySales'])->name('yearlySalesDetails');
});

//  Branch Auth Routes End



Route::get('/login', [HomeAuthController::class, 'getlogin'])->name('login');
Route::post('users/login', [HomeAuthController::class, 'userLogin']);
Route::get('getRegistor', [HomeAuthController::class, 'getRegistor'])->name('getRegistor');

// Home Register Route
Route::post('registerUser', [HomeAuthController::class, 'storeUser'])->name('registerUser');
Route::post('/user-reset-password-link', [HomeAuthController::class, 'userResetPasswordLink']);
Route::post('/user-reset-password', [HomeAuthController::class, 'resetPassword'])->name('user-reset-password');
Route::get('forgot-password', [HomeAuthController::class, 'forgotPassword'])->name('forgot-password');
Route::get('/userChange_password/{id}', [HomeAuthController::class, 'userChange_password'])->name('userChange_password');
//Home Register Routes End

Route::prefix('user')->middleware('user')->group(function () {
    Route::get('my-profile', [HomeAuthController::class, 'getProfile'])->name('my-profile');
    Route::post('update-profile/{id}', [HomeAuthController::class, 'updateProfile'])->name('update-profile');
    Route::get('my-order', [OrderController::class, 'myOrder'])->name('my-order');
    Route::post('order', [OrderController::class, 'order'])->name('orders');
    Route::get('user-logout', [HomeAuthController::class, 'userLogout'])->name('user-logout');
});

// notification seen route
Route::get('/mark-all-as-read', [OrderController::class, 'markAllAsRead'])->name('markAllAsRead');

Route::get('checkout', [CheckoutController::class, 'getCheckout'])->name('checkout');
Route::post('/store-tip-delivery', [CartController::class, 'storeTipAndDelivery'])->name('store.tip.delivery');
Route::get('getcontact', [HomeAuthController::class, 'getcontact'])->name('getcontact');
Route::get('refresh_captcha', [HomeAuthController::class,'refreshCaptcha'])->name('refresh_captcha');
Route::post('sendMail', [HomeController::class, 'sendMail'])->name('sendMail');

Route::get('/', [HomeController::class, 'index'])->name('index');
// Route::get('checkout', [CheckoutController::class, 'getCheckout'])->name('checkout');
Route::get('get-menu-picture', [HomeController::class, 'getmenuPicture'])->name('get-menu-picture');
Route::get('get-our-menu', [HomeController::class, 'getOurMenu'])->name('get-our-menu');
Route::get('get-our-gallery', [HomeController::class, 'getOurGallery'])->name('get-our-gallery');
Route::get('get-new-sea-moss', [HomeController::class, 'getNewSeaMoss'])->name('get-new-sea-moss');
// Serach route
Route::get('/search', [HomeController::class, 'search'])->name('product.search');

//cart routes
Route::get('my-cart', [CartController::class, 'myCart'])->name('my-cart');

Route::post('/update-order-status/{orderId}', [OrderController::class, 'updateStatus'])->name('update-order-status');

//security route
Route::get('get-faqs', [SecurityController::class, 'getFaqs'])->name('get-faqs');
Route::get('privacy-policy', [SecurityController::class, 'getPrivacyPolicy'])->name('privacy-policy');
Route::get('terms-conditions', [SecurityController::class, 'getTermCondition'])->name('terms-conditions');

//addtocart routes
Route::post('add-to-cart', [CartController::class, 'addToCart'])->name('add.to.cart');
Route::post('add-to-cart-remove', [CartController::class, 'remove'])->name('remove.from.cart');
Route::post('update-cart', [CartController::class, 'updateCart'])->name('update.cart');
Route::post('update-my-cart', [CartController::class, 'updateMyCartValue'])->name('update.my.cart');
Route::post('update-time', [CartController::class, 'updateTime'])->name('update.time');
Route::post('time-solt', [CartController::class, 'timeSlotes'])->name('time-solt');
Route::post('store.tip', [CartController::class, 'storeTipInSession'])->name('store.tip');
Route::post('store.vehicle.info', [CartController::class, 'storeVehicleInfo'])->name('store.vehicle.info');
Route::get('loyality-points' ,[LoyalityPointsController::class, 'index'])->name('loyality-points');

// brench update route

Route::post('/update-branch-status', [BranchUpdateController::class, 'updateBranchStatus'])
    ->name('update.branch.status');

