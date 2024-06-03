<?php

namespace App\Interfaces;

interface IProductRepository
{

    public function getAllPdo();

    public function deletePdo($id);

    public function softDeletePdo($rowsCollectId, $collectionLinesId);

    public function insertOrUpdatePdo($fields);

    public function getApi($url);

    public function insertOrUpdateApi($product);
}
