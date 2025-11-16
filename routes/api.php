<?php

use App\Models\Order\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Select\SelectController;
use App\Http\Controllers\Api\V1\Dashboard\Auth\AuthController;
use App\Http\Controllers\Api\V1\Dashboard\User\UserController;
use App\Http\Controllers\Api\V1\Dashboard\Areas\AreaController;
use App\Http\Controllers\Api\V1\Dashboard\Order\OrderController;
use App\Http\Controllers\Api\V1\Dashboard\Stats\StatsController;
use App\Http\Controllers\Api\V1\Dashboard\Client\ClientController;
use App\Http\Controllers\Api\V1\Dashboard\Slider\SliderController;
use App\Http\Controllers\Api\V1\Website\Order\AuthOrderController;
use App\Http\Controllers\Api\V1\Website\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Website\Auth\AuthWebsiteController;
use App\Http\Controllers\Api\V1\Dashboard\Product\ProductController;
use App\Http\Controllers\Api\V1\Website\Order\ClientOrderController;
// use App\Http\Controllers\Api\V1\Dashboard\Category\CategoryController;
use App\Http\Controllers\Api\V1\Dashboard\User\UserProfileController;
use App\Http\Controllers\Api\V1\Website\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Website\Order\AuthOrderItemController;
use App\Http\Controllers\Api\V1\Website\Order\CheckQuantityController;
use App\Http\Controllers\Api\V1\Dashboard\Client\ClientEmailController;
use App\Http\Controllers\Api\V1\Dashboard\Client\ClientPhoneController;
use App\Http\Controllers\Api\V1\Website\Client\ClientWebsiteController;
// use App\Http\Controllers\Api\V1\Dashboard\Category\SubCategoryController;
use App\Http\Controllers\Api\V1\Dashboard\Client\ClientAdressController;
use App\Http\Controllers\Api\V1\Dashboard\User\ChangePasswordController;
use App\Http\Controllers\Api\V1\Website\Order\OrderItemWebsiteController;
use App\Http\Controllers\Api\V1\Website\Product\ProductWebsiteController;
use App\Http\Controllers\Api\V1\Dashboard\StaticPage\StaticPageController;
use App\Http\Controllers\Api\V1\Website\StaticPage\StaticPageWebController;
use App\Http\Controllers\Api\V1\Website\Client\ClientEmailWebsiteController;
use App\Http\Controllers\Api\V1\Website\Client\ClientPhoneWebsiteController;
use App\Http\Controllers\Api\V1\Website\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Dashboard\MainCategory\CategoryTwoController;
use App\Http\Controllers\Api\V1\Website\Auth\Profile\ClientProfileController;
use App\Http\Controllers\Api\V1\Website\Client\ClientAdressWebsiteController;
use App\Http\Controllers\Api\V1\Website\Product\BestSellingProductController;
use App\Http\Controllers\Api\V1\Dashboard\Client\ClientCheckDefaultController;
use App\Http\Controllers\Api\V1\Dashboard\ProductMedia\ProductMediaController;
use App\Http\Controllers\Api\V1\Website\Order\OrderController as OrderWebsite;
use App\Http\Controllers\Api\V1\Website\Slider\SliderController as SliderWebsite;
use App\Http\Controllers\Api\V1\Dashboard\Notification\SendNotificationController;
use App\Http\Controllers\Api\V1\Website\Category\CategoryController as CategoryWebsite;
use App\Http\Controllers\Api\V1\Website\Auth\Profile\ChangePasswordController as ChangePasswordWebsite ;
//SendCodeController
Route::prefix('v1/admin')->group(function () {
    Route::post('/send-notification',SendNotificationController::class);
    Route::get('/pages/{slug}', [StaticPageController::class, 'show']);
    Route::put('/pages/{slug}', [StaticPageController::class, 'update']);
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login','login');
        Route::post('/logout','logout');
    });
    Route::post('/product-media/changeStatusMedia/{id}',[ProductMediaController::class ,'changeStatusProductMedia']);
    Route::post('/sliders/changeStatus/{id}',[SliderController::class ,'changeStatus']);
    Route::get('clients/clientCheckDefault',ClientCheckDefaultController::class);
    //force_delete_Client
    Route::post('clients/{id}/restore', [ClientController::class, 'restore']);
    Route::delete('clients/{id}/force', [ClientController::class, 'forceDelete']);
    Route::apiResources([
        // "categories" => CategoryController::class,
        // "sub-categories" =>SubCategoryController::class,
        "categories"=>CategoryTwoController::class,
        "product-media" => ProductMediaController::class,
        "products" => ProductController::class,
        "clients" => ClientController::class,
        "client-phones" => ClientPhoneController::class,
        "client-emails"=> ClientEmailController::class,
        "client-addresses"=>ClientAdressController::class,
        "orders" => OrderController::class,
        "sliders"=> SliderController::class,
        'areas' => AreaController::class,
    ]);
    Route::apiResource('users', UserController::class);
    Route::apiSingleton('profile', UserProfileController::class);
    Route::put('profile/change-password', ChangePasswordController::class);
    Route::get('/stats',StatsController::class);
    Route::prefix('selects')->group(function(){
        Route::get('', [SelectController::class, 'getSelects']);
      });

});//admin
Route::prefix('v1/website')->group(function(){
    Route::get('/pages/{slug}', [StaticPageWebController::class, 'show']);
    Route::controller(AuthWebsiteController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });
    Route::controller(ForgotPasswordController::class)->prefix("/forgotPassword")->group(function(){
        Route::post("sendCode","sendCodeEmail");
        Route::post('verifyCode','verifyCodeEmail');
        Route::post('resendCode','resendCode');
        Route::post('newPassword','newPassword');
    });
    Route::apiSingleton('profile', ClientProfileController::class)->names([
        'show' => 'profileWeb.show',
        'update'=>'profileWeb.update'
    ]);
    Route::post('logout',[AuthWebsiteController::class ,'logout'])->middleware('auth:client');
    Route::apiResource('client-orders',ClientOrderController::class)->only(['index','show']);
    Route::apiResource('sliders', SliderWebsite::class)->only(['index'])->names([
        'index' => 'slidersWeb.index',
    ]);
    Route::apiResource('categoryWebsite',CategoryWebsite::class)->only(['index']);
    Route::get('latest-products',[ProductWebsiteController::class ,'latestProducts']);
    Route::apiResource('products',ProductWebsiteController::class)->only(['index','show'])->names([
        'index' => 'productsWeb.index',
        'show'=>'productsWeb.show'
    ]);
    Route::apiResource('orders-web',OrderWebsite::class)->only(['store'])->names([
        'store' => 'ordersWeb.store',
    ]);//add orders-web

    Route::apiResource("clients-web" , ClientWebsiteController::class)->only(['index']);
    Route::apiResource("client-web-phones" , ClientPhoneWebsiteController::class);
    Route::apiResource("client-web-addresses",ClientAdressWebsiteController::class);
    Route::apiResource("client-web-emails",ClientEmailWebsiteController::class);
    Route::get('check-Quantity',CheckQuantityController::class);
    Route::put('change-password', ChangePasswordWebsite::class);
    Route::get('/BestSellingProducts',[BestSellingProductController::class ,'BestSellingProducts']);
    Route::get('/BestSellingProductsDetail/{id}',[BestSellingProductController::class ,'BestSellingProductsDetail']);
    Route::post('orders-auth',[AuthOrderController::class,'store']);
    Route::get('orders-auth/{id}',[AuthOrderController::class,'show']);
    Route::put('orders-update/{id}',[AuthOrderController::class,'update']);

    Route::prefix('orderItems')->group(function () {
        Route::get('', [OrderItemWebsiteController::class, 'allItems']);
        Route::post('{orderId}/items', [OrderItemWebsiteController::class, 'createItem']);
        Route::put('items/{itemId}', [OrderItemWebsiteController::class, 'updateItem']);
        Route::delete('items/{itemId}', [OrderItemWebsiteController::class, 'deleteItem']);
    });

    Route::post('/payment/process', [PaymentController::class, 'paymentProcess']);

    Route::get('/notifications',[NotificationController::class,'notifications']);
    Route::get('/auth_unread_notifications',[NotificationController::class,'auth_unread_notifications']);
    Route::get('/auth_read_notifications',[NotificationController::class,'auth_read_notifications']);
    Route::get('/auth_read_notification/{id}',[NotificationController::class,'auth_read_notification']);
    Route::DELETE('/auth_delete_notifications',[NotificationController::class,'auth_delete_notifications']);


});//website ...
// Route::match(['GET','POST'],'/payment/callback', [PaymentController::class, 'callBack']);
Route::match(['GET', 'POST'], '/payment/callback/paypal', [PaymentController::class, 'paypalCallback']);
Route::match(['GET', 'POST'], '/payment/callback/stripe', [PaymentController::class, 'stripeCallback']);

