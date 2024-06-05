<?php

namespace App\Jobs;

use App\Repository\ProductRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SoftDeleteProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $allProductArrayIds;
    /**
     * Create a new job instance.
     */
    public function __construct($allProductArrayIds)
    {
        $this->allProductArrayIds = $allProductArrayIds;
    }

    /**
     * Execute the job.
     */
    public function handle(ProductRepository $productRepository): void
    {
        info('SoftDeleteProductJob');
        $productRepository->softDeletePdo($this->allProductArrayIds);
    }
}
