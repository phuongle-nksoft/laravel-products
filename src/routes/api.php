<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => 'web', 'namespace' => 'Nksoft\Products\Controllers'], function () {
    Route::resources([
        '/' => CategoriesController::class,
        'categories' => CategoriesController::class,
        'products' => ProductsController::class,
        'customers' => CustomersController::class,
        'shippings' => ShippingsController::class,
        'orders' => OrdersController::class,
        'payments' => PaymentsController::class,
        'brands' => BrandsController::class,
        'professionals' => ProfessionalsController::class,
        'vintages' => VintagesController::class,
        'ratings' => RatingsController::class,
        'regions' => RegionsController::class,
    ]);
});
Route::group(['prefix' => 'api', 'namespace' => 'Nksoft\Products\Controllers'], function () {
    Route::get('products/list-filter', 'ProductsController@listFilter')->name('product-list-filter');
    Route::get('professional', 'ProfessionalsController@getAll')->name('professional');
});
