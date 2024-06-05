<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repository\ProductRepository;

class InsertOrUpdateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $fields;

    /**
     * Create a new job instance.
     */
    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Execute the job.
     */
    public function handle(ProductRepository $productRepository): void
    {
        info('InsertOrUpdateProductJob');
        $productRepository->insertOrUpdatePdo($this->fields);
    }
}
