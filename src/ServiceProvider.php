<?php

namespace MasterYuri\EditTrans;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Routing\Router;

use MasterYuri\EditTrans\Controller as EditTransHelper;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->commands(['MasterYuri\EditTrans\Commands\ViewsToLocales']);

        $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'edit-trans');
        
        $this->publishes(
        [
            __DIR__ . '/../config/admin-edit-trans.php' => config_path('admin-edit-trans.php'),
        ], 
        'config');

        $this->publishes(
        [
            __DIR__ . '/../public' => public_path('vendor/masteryuri/edit-trans'),
        ], 
        'public');
        
        $this->mergeConfigFrom(__DIR__ . '/../config/admin-edit-trans.php', 'admin-edit-trans');
        
        $this->setupRoutes($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $router->group
        (
            [
                'prefix'     => EditTransHelper::cfg('route.prefix'),
                'middleware' => EditTransHelper::cfg('route.middleware'),
            ], 
            function ($router)
            {
                $router->any('/edit_trans',        'MasterYuri\EditTrans\Controller@pageList');
                $router->any('/edit_trans/edit',   'MasterYuri\EditTrans\Controller@pageEdit');
                $router->any('/edit_trans/ckeditor4_upload', 'MasterYuri\EditTrans\Controller@uploadPhotoCKEditor4');
            }
        );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}