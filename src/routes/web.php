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
        ]);
    });
    Route::group(['namespace' => 'Nksoft\Products\Controllers'], function () {
        Route::post('add-cart', 'OrdersController@addCart')->name('add-cart');
        Route::get('get-cart', 'OrdersController@getCart')->name('get-cart');
        Route::delete('delete-cart/{rowId}', 'OrdersController@deteleCart')->name('delete-cart');
        Route::get('login/{service}/callback', 'CustomersController@callback');
        Route::get('login/{service}', 'CustomersController@loginSerices');
    });
});
Route::group(['namespace' => 'Nksoft\Products\Controllers'], function () {
    Route::get('categories/{id}', 'CategoriesController@show')->name('categories');
    Route::get('products/{id}', 'ProductsController@show')->name('products');
    Route::get('brands/{id}', 'BrandsController@show')->name('brands');
    Route::get('regions/{id}', 'RegionsController@show')->name('regions');
    Route::get('vintages/{id}', 'VintagesController@show')->name('vintages');
    // Route::post('add-cart', 'OrdersController@addCart')->name('add-cart');
});
