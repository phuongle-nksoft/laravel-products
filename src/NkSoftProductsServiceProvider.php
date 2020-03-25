<?php

namespace Nksoft\Products;

use Illuminate\Support\ServiceProvider;

class NkSoftProductsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/views', 'products');
        $this->loadTranslationsFrom(__DIR__ . '/language', 'nksoft');
        $this->publishes([
            __DIR__ . '/language' => resource_path('lang/vendor/nksoft'),
        ], 'nksoft');
        $this->mergeConfigFrom(__DIR__ . '/config/nksoft.php', 'nksoft');
        view()->composer('master::parts.sidebar', function ($view) {
            $view->with(['sidebar' => \Nksoft\Master\Models\Navigations::where(['is_active' => 1])->orderBy('order_by')->get()]);
        });
    }
}
