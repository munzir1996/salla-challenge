<?php

namespace Tests\Unit;

use App\Console\Commands\ImportProducts;
use App\Repository\ProductRepository;
use Mockery;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class ReadFileTest extends TestCase
{

    /** @test */
    public function can_upload_excel_file(): void
    {
        $productRepository = Mockery::mock(ProductRepository::class);
        $csvData = (new ImportProducts($productRepository))->readFile('products-test.csv');
        $this->assertNotEmpty($csvData);
    }
}
