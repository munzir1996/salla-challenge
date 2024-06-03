<?php

namespace App\Repository;

use App\Interfaces\IProductRepository;
use PDO;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProductRepository implements IProductRepository
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:dbname=coding_challenge;host=127.0.0.1;port=3306', 'root', '');
    }

    public function getAllPdo()
    {
        $query = $this->pdo->prepare("SELECT id from products WHERE deleted_at IS NULL");
        $result = $query->execute();

        return $query;
    }

    public function deletePdo($id)
    {
        $query = $this->pdo->prepare('DELETE FROM products WHERE id = ?');
        $result = $query->execute([$id]);

        return $result;
    }

    public function softDeletePdo($rowsCollectId, $collectionLinesId)
    {
        // Implement the soft delete logic here
        foreach ($rowsCollectId as $productId) {
            if (!in_array($productId, $collectionLinesId)) {
                $query = $this->pdo->prepare("UPDATE products SET deleted_at = NOW(), delete_hint = ? WHERE id = ?");
                $result = $query->execute([config('constants.deletionReason'), $productId]);
                if ($result) {
                    print("Soft Deleted product with ID $productId due to synchronization issue.");
                } else {
                    print("Error soft deleting product with ID $productId.");
                }
            }
        }
    }

    public function insertOrUpdatePdo($fields)
    {
        $query = $this->pdo->prepare('INSERT INTO products (id, name, sku, price, currency, variations, quantity, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE name=VALUES(name), sku=VALUES(sku), price=VALUES(price), currency=VALUES(currency), variations=VALUES(variations), quantity=VALUES(quantity), status=VALUES(status)');

        $result = $query->execute([$fields[0], ($fields[1] ?? ''), ($fields[2] ?? ''), ($fields[3] ?? ''), ($fields[4] ?? ''), ($fields[5] ?? ''), ($fields[6] ?? ''), ($fields[7] ?? '')]);

        return $result;
    }

    public function getApi($url)
    {
        $response = Http::get($url);
        $products = $response->json();

        if ($response->ok()) {
            return $products;
        }
    }
}
