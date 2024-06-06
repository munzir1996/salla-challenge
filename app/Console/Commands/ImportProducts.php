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
use Illuminate\Support\Facades\Cache;

use function PHPUnit\Framework\fileExists;
use function PHPUnit\Framework\isEmpty;

class ImportProducts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'import:products';

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

        // Insert file location to readFile function
        // dd('nigga');
        $lines = $this->readFile('products-test.csv');

        // Convert excel file data to a collection
        $collectionLines = new Collection($lines);
        // Divide the excel file into chuncks
        $collectionLinesChunck = $collectionLines->chunk(50);
        $collectionLinesId = $collectionLines->pluck(0)->toArray();

        // fetch products from DB
        $query = $this->productRepository->getAllPdo();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        // convert products rows into collection
        $rowsCollect = new Collection($rows);
        $rowsCollectId = $rowsCollect->pluck('id')->toArray();

        // start of the reading of the chunked files
        $collectionLinesChunck->each(function ($chunk) use ($rowsCollectId, $collectionLinesId) {

            // return an array of all DB ids that exist in the excel file ids
            $arrayIds = array_intersect($collectionLinesId, $rowsCollectId);
            // dispatch a job to delete all the returned ids in $arrayIds
            DeleteProductJob::dispatch($arrayIds)->onQueue('deletePdo');


            $chunk->each(function ($fields) {
                // Insert or Update the products in excel file
                InsertOrUpdateProductJob::dispatch($fields)->onQueue('insertUpdatePdo');
            });
        });

        // Soft delete products no longer in the file

        // get all the Db product IDs that doesnt exist in the excel file.
        $productArrayIds = collect(array_diff($rowsCollectId, $collectionLinesId));
        // filter all products id that its status is equal to delete
        $deletedProductsIds = $rowsCollect->filter(function ($value, int $key) {
            return $value['status'] == 'deleted';
        })->pluck('id');
        //Merge the $productArrayIds and $deletedProductsIds
        $allProductArrayIds = $productArrayIds->merge($deletedProductsIds);

        //Dispatch a job to soft delete all $allProductArrayIds
        SoftDeleteProductJob::dispatch($allProductArrayIds)->onQueue('softDeletePdo');
        $this->info('Updated ' . $this->i . ' products.');
    }

    public function readFile($fileName)
    {
        // validate that the selected file is an excel
        $validator = Validator::make([
            'file' => $fileName
        ], [
            'file' => ['required', new excelFileRule],
        ]);

        $validator->validate();

        if (file_exists($fileName)) {
            // read the file data from the excel
            $csvData = array_map('str_getcsv', file($fileName));
            // skip the first row in the excel file
            array_shift($csvData);
            return $csvData;
        } else {
            die("The file $fileName does not exist or cannot be read.");
        }
    }
}
