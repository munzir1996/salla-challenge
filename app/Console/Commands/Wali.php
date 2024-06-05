<?php

namespace App\Console\Commands;

use App\Repository\ProductRepository;
use App\Rules\excelFileRule;
use Illuminate\Console\Command;
use PDO;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use App\Interfaces\IProductRepository;
use App\Jobs\DeleteProductJob;
use App\Jobs\InsertOrUpdateProductJob;
use App\Jobs\SoftDeleteProductJob;

use function PHPUnit\Framework\fileExists;
use function PHPUnit\Framework\isEmpty;

class Wali extends Command
{
    /**
     * @var string
     */
    protected $signature = 'import:wali';

    /**
     * @var string
     */
    protected $description = 'Imports products into database';

    protected $i = 0;
    /**
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository)
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        // Rename from products1.csv into products2.csv to import a file with slightly different data
        $lines = $this->readFile('products-test.csv');

        $collectionLines = new Collection($lines);
        $collectionLinesChunck = $collectionLines->chunk(50);
        $collectionLinesId = $collectionLines->pluck(0)->toArray();

        $query = $this->productRepository->getAllPdo();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $rowsCollect = new Collection($rows);
        $rowsCollectId = $rowsCollect->pluck('id')->toArray();

        $deletedProductsIds = $rowsCollect->filter(function ($value, int $key) {
            return $value['status'] == 'deleted';
        })->pluck('id');

        $collectionLinesChunck->each(function ($chunk) use ($rowsCollectId, $collectionLinesId) {

            $arrayIds = array_intersect($collectionLinesId, $rowsCollectId);
            //dispatch Job
            DeleteProductJob::dispatch($arrayIds)->onQueue('deletePdo');
            //dispatch Job

            // if (!empty($arrayIds)) {

            //     $result = $this->productRepository->deletePdo($arrayIds, $this->productRepository);

            //     if ($result) {
            //         $this->info("Deleted existing product with ID.");
            //     } else {
            //         $this->error("Error deleting product with ID.");
            //     }
            // }


            $chunk->each(function ($fields) {

                // dispatch Job
                InsertOrUpdateProductJob::dispatch($fields)->onQueue('insertUpdatePdo');
                //dispatch Job
                // $result = $this->productRepository->insertOrUpdatePdo($fields);

                // if ($result) {
                //     $this->i++;
                // }
            });
        });

        // Soft delete products no longer in the file

        $productArrayIds = collect(array_diff($rowsCollectId, $collectionLinesId));
        $allProductArrayIds = $productArrayIds->merge($deletedProductsIds);
        // dd($allProductArrayIds);
        //dispatch Job
        SoftDeleteProductJob::dispatch($allProductArrayIds)->onQueue('softDeletePdo');
        //dispatch Job
        // $this->productRepository->softDeletePdo($allProductArrayIds);
        $this->info('Updated ' . $this->i . ' products.');
    }

    public function readFile($fileName)
    {
        $validator = Validator::make([
            'file' => $fileName
        ], [
            'file' => ['required', new excelFileRule],
        ]);

        $validator->validate();

        if (file_exists($fileName)) {
            $csvData = array_map('str_getcsv', file($fileName));
            array_shift($csvData);
            return $csvData;
        } else {
            die("The file $fileName does not exist or cannot be read.");
        }
    }
}
