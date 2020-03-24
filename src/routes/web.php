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
        ]);
    });
});
