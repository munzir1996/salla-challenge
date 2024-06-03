<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\IProductRepository;
use App\Repository\ProductRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IProductRepository::class, ProductRepository::class);
        // $this->app->bind(UserService::class, function ($app) {
        //     return new UserService($app->make(UserRepositoryInterface::class));
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
