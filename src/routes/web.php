<?php

use Illuminate\Support\Facades\Route;
use Nksoft\Master\Controllers\WebController;

Route::group(['middleware' => 'web'], function () {
    Route::group(['middleware' => 'nksoft', 'prefix' => 'admin'], function () {
        Route::resources([
            'categories' => WebController::class,
            'products' => WebController::class,
            'customers' => WebController::class,
            'shippings' => WebController::class,
            'orders' => WebController::class,
            'payments' => WebController::class,
            'brands' => WebController::class,
            'regions' => WebController::class,
            'vintages' => WebController::class,
            'professionals' => WebController::class,
            'promotions' => WebController::class,
            'tags' => WebController::class,
            'comments' => WebController::class,
            'discoveries' => WebController::class,
            'types' => WebController::class,
        ]);
    });
    Route::group(['namespace' => 'Nksoft\Products\Controllers'], function () {
        Route::post('add-cart', 'OrdersController@addCart')->name('add-cart');
        Route::get('get-cart', 'OrdersController@getCart')->name('get-cart');
        Route::delete('delete-cart/{rowId}', 'OrdersController@deteleCart')->name('delete-cart');
        Route::post('discount', 'OrdersController@discount');
        Route::get('getDiscount', 'OrdersController@getDiscount');
        Route::post('deleteCode', 'OrdersController@deleteCode');
        Route::get('myHistory', 'CustomersController@histories');
        Route::get('getUser', 'CustomersController@getUser');
        Route::get('login/{service}/callback', 'CustomersController@callback');
        Route::get('login/{service}', 'CustomersController@loginSerices');
        Route::post('customers/login', 'CustomersController@login');
        Route::post('customers/register', 'CustomersController@store');
        Route::get('logout', 'CustomersController@logout');
        Route::get('myWine', 'CustomersController@myWine');
        Route::get('notifications', 'NotificationController@index');
        Route::post('shippings', 'ShippingsController@store');
        Route::put('shippings/{id}', 'ShippingsController@update');
        Route::delete('shippings/{id}', 'ShippingsController@destroy');
        Route::get('provinces', 'ShippingsController@getProvinces');
        Route::post('payments', 'PaymentsController@store');
        Route::get('payments/{service}/callback', 'PaymentsController@callback');
        Route::get('payments/{service}/ipn', 'PaymentsController@save');
        Route::post('addWishlist', 'ProductsController@addWishlist');
        Route::delete('deleteWishlist/{wishlistId}', 'ProductsController@deleteWishlist');
        Route::post('addComment', 'ProductsController@addComment');
        Route::get('getComment/{productId}', 'ProductsController@getComment');
        Route::get('home', 'ProductsController@getHome');
        Route::get('resVnpay', 'PaymentsController@resVnpay');
        Route::resources([
            'customers' => CustomersController::class,
        ]);
    });
});
Route::group(['namespace' => 'Nksoft\Products\Controllers'], function () {
    Route::get('categories/{id}', 'CategoriesController@show')->name('categories');
    Route::get('products/{id}', 'ProductsController@show')->name('products');
    Route::get('filter/{slug}', 'ProductsController@filter')->name('products.filter');
    Route::get('brands/{id}', 'BrandsController@show')->name('brands');
    Route::get('regions/{id}', 'RegionsController@show')->name('regions');
    Route::get('vintages/{id}', 'VintagesController@show')->name('vintages');
    // Route::post('add-cart', 'OrdersController@addCart')->name('add-cart');
});
