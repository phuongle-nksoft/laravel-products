<?php

use Illuminate\Support\Facades\Route;
use Nksoft\Master\Controllers\WebController;

Route::group(['middleware' => 'web'], function () {
    Route::get('login', function () {
        return view('master::modules.users.login');
    });
    Route::post('login', '\Nksoft\Master\Controllers\UsersController@login');
    Route::group(['middleware' => 'nksoft', 'prefix' => 'admin'], function () {
        Route::get('logout', '\Nksoft\Master\Controllers\UsersController@logout');
        Route::get('settings', '\Nksoft\Master\Controllers\WebController@create');
        Route::resources([
            '/' => WebController::class,
            'dashboard' => WebController::class,
            'users' => WebController::class,
            'roles' => WebController::class,
        ]);
    });
});
