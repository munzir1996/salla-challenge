<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Console\Commands\ImportProducts;
use App\Repository\ProductRepository;
use Mockery;

class ImportProductTest extends TestCase
{
    /** @test */
    public function can_import_products_through_excel_file(): void
    {
        // $productRepository = Mockery::mock(ProductRepository::class);
        // $csvData = (new ImportProducts($productRepository))->handle();
        // $this->assertNotEmpty($csvData);

        // $response->assertStatus(200);
    }
}
