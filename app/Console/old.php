<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class ImportProducts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'import:product';

    /**
     * @var string
     */
    protected $description = 'Imports products into database';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        // Rename from products1.csv into products2.csv to import a file with slightly different data
        $contents = file_get_contents('products.csv');
        $lines = explode("\n", $contents);

        $i = 0;

        foreach ($lines as $line) {

            $fields = explode(';', $line);

            $pdo = new PDO('mysql:dbname=coding_challenge;host=172.0.0.1;port=3306', 'root', 'secret');

            $query = $pdo->prepare("SELECT COUNT(*) AS c from products WHERE id=?");
            $result = $query->execute([$fields[0]]);
            if ($query->rowCount()) {
                $pdo->query('DELETE FROM products WHERE id = "' . $fields[0] . '"');
                print('Deleted existed product to be updated.');
            }

            $query = $pdo->prepare('INSERT INTO products (id, name, sku,status,variations, price, currency) VALUES (?, ?, ?, ? , ? , ?, ?)');
            $result = $query->execute([$fields[0], ($fields[4] ?? ''), ($fields[5] ?? ''), ($fields[6] ?? ''), ($fields[9] ?? ''), ($fields[10] ?? '')]);

            // TODO: Soft delete no longer exist products  the database.

            $i++;
        }

        die('Updated ' . $i . ' products.');
    }
}
