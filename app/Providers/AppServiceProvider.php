<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\IProductRepository;
use App\Jobs\DeleteProductJob;
use App\Jobs\InsertOrUpdateProductJob;
use App\Jobs\SoftDeleteProductJob;
use App\Repository\ProductRepository;
use Tests\Unit\ProductApiTest;
use Tests\Unit\ReadFileTest;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
