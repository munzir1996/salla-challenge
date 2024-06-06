<?php

namespace Tests\Unit;

use App\Interfaces\IProductRepository;
use Tests\TestCase;
use Database\Seeders\ProductTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductPdoTest extends TestCase
{
    /** @test */
    public function can_get_all_products_pdo(): void
    {

        $seeder = new ProductTableSeeder();

        $seeder->run();

        $productRepository = app(IProductRepository::class);
        $query = $productRepository->getAllPdo();
        $result = $query->execute();
        $this->assertTrue($result);
    }

    /** @test */
    public function can_delete_products_pdo(): void
    {

        $seeder = new ProductTableSeeder();

        $seeder->run();

        $productRepository = app(IProductRepository::class);
        $result = $productRepository->deletePdo([1]);
        $this->assertTrue($result);
        // $this->assertDatabaseEmpty('products');
    }
}
