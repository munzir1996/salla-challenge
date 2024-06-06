<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;

use App\Interfaces\IProductRepository;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    /** @test */
    public function can_get_api_products(): void
    {

        $productRepository = app(IProductRepository::class);
        $products = $productRepository->getApi('https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products');

        $this->assertNotEmpty($products);
    }
}
