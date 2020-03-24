<?php
use Nksoft\Products\Controllers\CategoriesController;
use Nksoft\Products\Controllers\CustomersController;
use Nksoft\Products\Controllers\OrdersController;
use Nksoft\Products\Controllers\PaymentsController;
use Nksoft\Products\Controllers\ProductsController;
use Nksoft\Products\Controllers\ShippingsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/admin', 'middleware' => 'api'], function () {
    Route::resources([
        'categories' => CategoriesController::class,
        'products' => ProductsController::class,
        'customers' => CustomersController::class,
        'shippings' => ShippingsController::class,
        'orders' => OrdersController::class,
        'payments' => PaymentsController::class,
    ]);
});
