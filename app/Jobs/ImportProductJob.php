<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repository\ProductRepository;

class ImportProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $arrayIds;
    /**
     * Create a new job instance.
     */
    public function __construct($arrayIds, protected ProductRepository $productRepository)
    {
        $this->arrayIds = $arrayIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
