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

    protected $deletionReason = 'Synchronization issue';

    // protected $pdo;

    /**
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository)
    {
        parent::__construct();

        // $this->pdo = new PDO('mysql:dbname=coding_challenge;host=127.0.0.1;port=3306', 'root', '');
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
        // $query = $this->pdo->prepare("SELECT id from products WHERE deleted_at IS NULL");
        // $result = $query->execute();
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
                    // $query = $this->pdo->prepare('DELETE FROM products WHERE id = ?');
                    // $result = $query->execute([$productID]);

                    if ($result) {
                        $this->info("Deleted existing product with ID $productID.");
                    } else {
                        $this->error("Error deleting product with ID $productID.");
                    }
                }

                // Insert new or update product

                $result = $this->productRepository->insertOrUpdatePdo($fields);
                // $query = $this->pdo->prepare('INSERT INTO products (id, name, sku, price, currency, variations, quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                //     ON DUPLICATE KEY UPDATE name=VALUES(name), sku=VALUES(sku), price=VALUES(price), currency=VALUES(currency), variations=VALUES(variations), quantity=VALUES(quantity), status=VALUES(status)');
                // $result = $query->execute([$fields[0], ($fields[1] ?? ''), ($fields[2] ?? ''), ($fields[3] ?? ''), ($fields[4] ?? ''), ($fields[5] ?? ''), ($fields[6] ?? ''), ($fields[7] ?? '')]);

                if ($result) {
                    $i++;
                }
            }
        }

        // Soft delete products no longer in the file
        $this->productRepository->softDeletePdo($rowsCollectId, $collectionLinesId);
        // $this->softDelete($rowsCollectId, $collectionLinesId);

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

    // public function softDelete($rowsCollectId, $collectionLinesId)
    // {

    //     foreach ($rowsCollectId as $productId) {
    //         if (!in_array($productId, $collectionLinesId)) {
    //             $query = $this->pdo->prepare("UPDATE products SET deleted_at = NOW(), delete_hint = ? WHERE id = ?");
    //             $result = $query->execute([$this->deletionReason, $productId]);
    //             if ($result) {
    //                 $this->info("Soft Deleted product with ID $productId due to synchronization issue.");
    //             } else {
    //                 $this->error("Error soft deleting product with ID $productId.");
    //             }
    //         }
    //     }
    // }
}
