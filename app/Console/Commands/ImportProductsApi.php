<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\ProductRepository;
use Illuminate\Support\Collection;
use PDO;

class ImportProductsApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected ProductRepository $productRepository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = $this->productRepository->getApi('https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products');

        // dd($products);

        // $lines = $this->readFile('products-test.csv');

        // $collectionLines = new Collection($products);
        // $collectionProductsId = $collectionLines->pluck(0)->toArray();

        $collectionProducts = new Collection($products);
        $collectionProductsId = $collectionProducts->pluck('id')->toArray();

        // dd($collectionProductsId);

        $query = $this->productRepository->getAllPdo();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $rowsCollect = new Collection($rows);
        $rowsCollectId = $rowsCollect->pluck('id')->toArray();

        $i = 0;

        foreach ($products as $index => $fields) {

            // if ($index > 0) {
            dd($fields['id']);
            // Process each product line from CSV
            $productID = $fields['id'];

            if (in_array($productID, $rowsCollectId)) {
                // Update existing product
                $result = $this->productRepository->deletePdo($productID);

                if ($result) {
                    $this->info("Deleted existing product with ID $productID.");
                } else {
                    $this->error("Error deleting product with ID $productID.");
                }
            }

            // Insert new or update product

            $result = $this->productRepository->insertOrUpdatePdo($fields);

            if ($result) {
                $i++;
            }
            // }
        }

        // Soft delete products no longer in the file
        // $this->productRepository->softDeletePdo($rowsCollectId, $collectionLinesId);
        $this->info('Updated ' . $i . ' products.');
    }
}
