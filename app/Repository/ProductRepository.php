<?php

namespace App\Repository;

use App\Interfaces\IProductRepository;
use Exception;
use Illuminate\Support\Facades\DB;
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
        $query = $this->pdo->prepare("SELECT id, status from products WHERE deleted_at IS NULL");
        $result = $query->execute();

        return $query;
    }

    public function deletePdo($arrayId)
    {
        $ids = implode("','", $arrayId);
        $query = $this->pdo->prepare("DELETE FROM products WHERE id IN ('" . $ids . "')");
        $result = $query->execute();

        return $result;
    }

    public function softDeletePdo($productArrayIds)
    {
        // Implement the soft delete logic here
        if (!empty($productArrayIds)) {
            foreach ($productArrayIds as $rowProductId) {
                $query = $this->pdo->prepare("UPDATE products SET deleted_at = NOW(), delete_hint = ? WHERE id = ?");
                $result = $query->execute([config('constants.deletionReason'), $rowProductId]);
                if ($result) {
                    print("Soft Deleted product with ID $rowProductId due to synchronization issue.");
                } else {
                    print("Error soft deleting product with ID $rowProductId.");
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

        return $response->badRequest();
    }

    public function insertOrUpdateApi($product)
    {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo->beginTransaction();
            $query = $this->pdo->prepare("UPDATE products SET name=?, price=?, variations=? WHERE id=? AND deleted_at IS NULL");

            $result = $query->execute([($product['name'] ?? ''), ($product['price'] ?? ''), (json_encode($product['variations']) ?? ''), $product['id']]);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "Failed: " . $e->getMessage();
            return false;
        }
    }
}
