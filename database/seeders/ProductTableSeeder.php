<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('products')->insert([
            'name' => Str::random(10),
            'sku' => Str::random(10),
            'price' => 512,
            'currency' => Str::random(10),
            'variations' => Str::random(10),
            'quantity' => 52,
            'status' => Str::random(10),
            'deleted_at' => null,
            'delete_hint' => null,
        ]);
    }
}
