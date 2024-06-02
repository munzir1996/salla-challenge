<?php

namespace App\Console\Commands;

use App\Rules\excelFileRule;
use Illuminate\Console\Command;
use PDO;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

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

    protected $pdo;

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->pdo = new PDO('mysql:dbname=coding_challenge;host=127.0.0.1;port=3306', 'root', '');
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        // Rename from products1.csv into products2.csv to import a file with slightly different data

        // $pdo = new PDO('mysql:dbname=coding_challenge;host=127.0.0.1;port=3306', 'root', '');

        // $filePath = (base_path() . "\\" . $fileName);

        // $contents = file_get_contents($fileName);

        // $lines = explode("\r\n", $contents);

        $lines = $this->readFile('products-test.csv');

        $collectionLines = new Collection($lines);
        $collectionLinesId = $collectionLines->pluck(0)->toArray();

        $query = $this->pdo->prepare("SELECT id from products WHERE deleted_at IS NULL");
        $result = $query->execute();
        $rows = $query->fetchAll();

        $rowsCollect = new Collection($rows);
        $rowsCollectId = $rowsCollect->pluck('id')->toArray();

        $i = 0;

        foreach ($lines as $fields) {

            if ($i > 0) {

                foreach ($rowsCollectId as $productId) {

                    if (!in_array($productId, $collectionLinesId)) {
                        //Delete
                        $query = $this->pdo->prepare("UPDATE products SET deleted_at ='" . now() . "', delete_hint = 'Synchronization issue' WHERE id = ?");
                        $result = $query->execute([$productId]);
                        if ($result) {
                            print("Soft Deleted product with ID $productId." . PHP_EOL);
                        } else {
                            print("Error Soft deleting product with ID $productId." . PHP_EOL);
                        }
                        /* Product no longer in Excel, mark as deleted You can update the 'deleted' flag in your database
                         Example: UPDATE products SET deleted = 1 WHERE id = :productId */
                    } else {

                        // dd("product found");
                        // Delete record if found
                        $productID = $fields[0];

                        // UPDATE products SET is_deleted = 1, deletion_reason = :reason WHERE id = :id
                        $query = $this->pdo->prepare('DELETE FROM products WHERE id = ?');

                        $result = $query->execute([$productID]);

                        if ($result) {
                            print("Deleted existing product with ID $productID." . PHP_EOL);
                        } else {
                            print("Error deleting product with ID $productID." . PHP_EOL);
                        }
                    }
                }

                //Inser All records
                $query = $this->pdo->prepare('INSERT INTO products (id, name, sku, price, currency, variations, quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $result = $query->execute([$fields[0], ($fields[1] ?? ''), ($fields[2] ?? ''), ($fields[3] ?? ''), ($fields[4] ?? ''), ($fields[5] ?? ''), ($fields[6] ?? ''), ($fields[7] ?? '')]);

                // TODO: Soft delete no longer exist products from the database.
                // Modify the import command to soft delete any products no longer in the file
                // (not in the file or flagged as deleted). Add a hint to the deleted record indicating the product was
                // deleted due to synchronizationModify the import command to soft delete any products no longer in the file
            }

            $i++;
        }

        die('Updated ' . $i . ' products.');
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
