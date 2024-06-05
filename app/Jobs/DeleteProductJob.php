<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repository\ProductRepository;

class DeleteProductJob implements ShouldQueue
{
    //Batchable
    //queue:batches-table
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $arrayIds;
    /**
     * Create a new job instance.
     */

    // php artisan queue:work --queue=payments,default
    /*
        $chain = [1,2,3]
        Bus::chain()->dispatch();
    */

    public function __construct($arrayIds)
    {
        $this->arrayIds = $arrayIds;
    }

    /**
     * Execute the job.
     */
    public function handle(ProductRepository $productRepository): void
    {
        info('ImportProductJob');
        if (!empty($this->arrayIds)) {

            $result = $productRepository->deletePdo($this->arrayIds);
            info('productRepository');

            if ($result) {
                info("Deleted existing product with ID.");
            } else {
                info("Error deleting product with ID.");
            }
        }
    }
}
