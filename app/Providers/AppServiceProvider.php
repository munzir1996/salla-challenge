<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\IProductRepository;
use App\Jobs\DeleteProductJob;
use App\Jobs\InsertOrUpdateProductJob;
use App\Jobs\SoftDeleteProductJob;
use App\Repository\ProductRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IProductRepository::class, ProductRepository::class);
        $this->app->bindMethod(DeleteProductJob::class . '@handle', function ($job, $app) {
            return $job->handle($app->make(ProductRepository::class));
        });
        $this->app->bindMethod(InsertOrUpdateProductJob::class . '@handle', function ($job, $app) {
            return $job->handle($app->make(ProductRepository::class));
        });
        $this->app->bindMethod(SoftDeleteProductJob::class . '@handle', function ($job, $app) {
            return $job->handle($app->make(ProductRepository::class));
        });
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
