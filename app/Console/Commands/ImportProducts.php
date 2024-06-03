<?php

namespace App\Console\Commands;

use App\Repository\ProductRepository;
use App\Rules\excelFileRule;
use Illuminate\Console\Command;
use PDO;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Interfaces\IProductRepository;

use function PHPUnit\Framework\fileExists;

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
        $collectionLinesId = $collectionLines->pluck(0)->toArray();

        $query = $this->productRepository->getAllPdo();
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $rowsCollect = new Collection($rows);
        $rowsCollectId = $rowsCollect->pluck('id')->toArray();

        $i = 0;

        foreach ($lines as $index => $fields) {

            if ($index > 0) {

                // Process each product line from CSV
                $productID = $fields[0];

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
            }
        }

        // Soft delete products no longer in the file
        $this->productRepository->softDeletePdo($rowsCollectId, $collectionLinesId);
        $this->info('Updated ' . $i . ' products.');
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

            return $csvData;
        } else {
            die("The file $fileName does not exist or cannot be read.");
        }
    }
}
