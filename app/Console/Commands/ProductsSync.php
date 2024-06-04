<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\ProductRepository;
use Illuminate\Support\Collection;
use PDO;

class ProductsSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync';

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

        // $collectionProducts = new Collection($products);
        // $collectionProductsId = $collectionProducts->pluck('id')->toArray();

        $query = $this->productRepository->getAllPdo();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $rowsCollect = new Collection($rows);
        $rowsCollectId = $rowsCollect->pluck('id')->toArray();

        $i = 0;

        foreach ($products as $index => $product) {

            $productID = $product['id'];

            if (in_array($productID, $rowsCollectId)) {

                $result = $this->productRepository->insertOrUpdateApi($product);

                if ($result) {
                    $this->info("Updated existing product with ID $productID.");
                    $i++;
                } else {
                    $this->error("Error Updateing product with ID $productID.");
                }
            }
        }

        $this->info('Updated ' . $i . ' products.');
    }
}
