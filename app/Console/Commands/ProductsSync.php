<?php

namespace App\Console\Commands;

use App\Jobs\customerJob;
use App\Jobs\warehouseJob;
use App\Jobs\apiUpdateJob;
use Illuminate\Console\Command;
use App\Repository\ProductRepository;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use PDO;
use Throwable;

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
        // Read the products from the API endpoint
        $products = $this->productRepository->getApi('https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products');

        // If there is products in API endpoint
        if ($products) {

            // Fetch all the products from the DB
            $query = $this->productRepository->getAllPdo();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

            // Convert rows to collection
            $rowsCollect = new Collection($rows);
            $rowsCollectId = $rowsCollect->pluck('id')->toArray();

            $i = 0;

            foreach ($products as $index => $product) {

                $productID = $product['id'];

                // If API products Id is found in the Products DB
                if (in_array($productID, $rowsCollectId)) {

                    // Insert Or update the products DB
                    $result = $this->productRepository->insertOrUpdateApi($product);
                    // Dispatch a job to specific intrested users after the DB commit
                    if ($result) {
                        $batch = Bus::batch([
                            new customerJob(),
                            new warehouseJob(),
                            new apiUpdateJob()
                        ])->dispatch();
                        $this->info("Updated existing product with ID $productID.");
                        $i++;
                    } else {
                        $this->error("Error Updateing product with ID $productID.");
                    }
                }
            }

            $this->info('Updated ' . $i . ' products.');
        } else {
            $this->error("Error updating");
        }
    }
}
