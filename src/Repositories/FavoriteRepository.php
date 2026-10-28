<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FavoriteRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Saving twice is harmless: a duplicate (user_id, recipe_id) just keeps the existing row.
     * Not INSERT IGNORE, because that would also hide real errors such as a foreign key failure.
     */
    public function add(int $userId, int $recipeId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO favorites (user_id, recipe_id) VALUES (:user, :recipe)
             ON DUPLICATE KEY UPDATE created_at = created_at'
        );
        $stmt->execute([':user' => $userId, ':recipe' => $recipeId]);
    }

    public function remove(int $userId, int $recipeId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM favorites WHERE user_id = :user AND recipe_id = :recipe');
        $stmt->execute([':user' => $userId, ':recipe' => $recipeId]);
    }

    public function isFavorite(int $userId, int $recipeId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM favorites WHERE user_id = :user AND recipe_id = :recipe');
        $stmt->execute([':user' => $userId, ':recipe' => $recipeId]);
        return (bool) $stmt->fetchColumn();
    }

    public function countFor(int $recipeId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM favorites WHERE recipe_id = :recipe');
        $stmt->execute([':recipe' => $recipeId]);
        return (int) $stmt->fetchColumn();
    }
}
