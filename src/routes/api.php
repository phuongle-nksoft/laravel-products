<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => 'web', 'namespace' => 'Nksoft\Products\Controllers'], function () {
    Route::get('categories/search', 'CategoriesController@index');
    Route::get('products/search', 'ProductsController@index');
    Route::get('vintages/search', 'VintagesController@index');
    Route::get('brands/search', 'BrandsController@index');
    Route::get('regions/search', 'RegionsController@index');
    Route::get('customers/search', 'CustomersController@index');
    Route::get('orders/search', 'OrdersController@index');
    Route::resources([
        '/' => OrdersController::class,
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
        'promotions' => PromotionsController::class,
        'tags' => TagsController::class,
        'comments' => ProductCommentController::class,
    ]);
});
Route::group(['prefix' => 'api', 'namespace' => 'Nksoft\Products\Controllers'], function () {
    Route::get('products/list-filter', 'ProductsController@listFilter')->name('product-list-filter');
    Route::get('professional', 'ProfessionalsController@getAll')->name('professional');
    Route::get('tim-kiem', 'ProductsController@getSearch')->name('search');
});
