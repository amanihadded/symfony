<?php

namespace App\Service;

class ProductService
{
    private array $products = [
        1 => ['id' => 1, 'name' => 'Laptop', 'price' => 1500],
        2 => ['id' => 2, 'name' => 'Smartphone', 'price' => 800],
        3 => ['id' => 3, 'name' => 'Tablet', 'price' => 500],
    ];

    public function getAll(): array
    {
        return $this->products;
    }

    public function getById(int $id): ?array
    {
        return $this->products[$id] ?? null;
    }
}