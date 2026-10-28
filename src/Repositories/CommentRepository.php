<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CommentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Oldest first so the conversation reads top to bottom. */
    public function forRecipe(int $recipeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cm.id, cm.user_id, cm.body, cm.created_at, cm.updated_at, u.display_name AS author
             FROM comments cm
             INNER JOIN users u ON u.id = cm.user_id
             WHERE cm.recipe_id = :recipe
             ORDER BY cm.created_at, cm.id'
        );
        $stmt->execute([':recipe' => $recipeId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $recipeId, int $userId, string $body): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO comments (recipe_id, user_id, body) VALUES (:recipe, :user, :body)');
        $stmt->execute([':recipe' => $recipeId, ':user' => $userId, ':body' => $body]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * The user_id condition means a member can only ever change their own comment,
     * even if someone tampers with the comment ID in the form.
     */
    public function update(int $id, int $userId, string $body): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE comments SET body = :body, updated_at = NOW() WHERE id = :id AND user_id = :user'
        );
        $stmt->execute([':body' => $body, ':id' => $id, ':user' => $userId]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE id = :id AND user_id = :user');
        $stmt->execute([':id' => $id, ':user' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
