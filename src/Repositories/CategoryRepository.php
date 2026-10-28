<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CategoryRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** All categories with how many recipes each has (LEFT JOIN keeps empty ones). */
    public function allWithCounts(): array
    {
        return $this->pdo->query(
            'SELECT c.id, c.name, c.description, COUNT(r.id) AS recipe_count
             FROM categories c
             LEFT JOIN recipes r ON r.category_id = c.id
             GROUP BY c.id, c.name, c.description
             ORDER BY c.id'
        )->fetchAll();
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT id, name, description FROM categories ORDER BY id')->fetchAll();
    }

    /** @return list<int> */
    public function ids(): array
    {
        return array_map('intval', $this->pdo->query('SELECT id FROM categories')->fetchAll(PDO::FETCH_COLUMN));
    }
}
