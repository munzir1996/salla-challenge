<?php

namespace App\Interfaces;

interface IProductRepository
{

    public function getAllPdo();

    public function deletePdo($id);

    public function softDeletePdo($productArrayIds);

    public function insertOrUpdatePdo($fields);

    public function getApi($url);

    public function insertOrUpdateApi($product);
}
