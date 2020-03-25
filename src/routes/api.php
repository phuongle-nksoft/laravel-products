<?php
use Illuminate\Support\Facades\Route;
use Nksoft\Products\Controllers\BrandsController;
use Nksoft\Products\Controllers\CategoriesController;
use Nksoft\Products\Controllers\CustomersController;
use Nksoft\Products\Controllers\OrdersController;
use Nksoft\Products\Controllers\PaymentsController;
use Nksoft\Products\Controllers\ProductsController;
use Nksoft\Products\Controllers\ProfessionalsController;
use Nksoft\Products\Controllers\RatingsController;
use Nksoft\Products\Controllers\RegionsController;
use Nksoft\Products\Controllers\ShippingsController;
use Nksoft\Products\Controllers\VintagesController;

Route::group(['prefix' => 'api/admin', 'middleware' => 'api'], function () {
    Route::resources([
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
