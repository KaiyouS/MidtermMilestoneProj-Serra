<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOStatement;
use Throwable;

final class RecipeRepository
{
    /** Shared by the list and detail queries: author, category, and counts in one round trip. */
    private const SELECT = '
        SELECT r.id, r.user_id, r.category_id, r.title, r.description, r.servings, r.steps,
               r.created_at, r.updated_at,
               u.display_name AS author,
               c.name AS category,
               (SELECT COUNT(*) FROM favorites f WHERE f.recipe_id = r.id) AS favorite_count,
               (SELECT COUNT(*) FROM comments cm WHERE cm.recipe_id = r.id) AS comment_count,
               EXISTS (SELECT 1 FROM favorites mf WHERE mf.recipe_id = r.id AND mf.user_id = :viewer) AS is_favorite
        FROM recipes r
        INNER JOIN users u ON u.id = r.user_id
        INNER JOIN categories c ON c.id = r.category_id';

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Latest recipes first, optionally filtered.
     *
     * @param array{q?: string, category?: ?int, author?: ?int, favorites_of?: ?int} $filters
     */
    public function search(array $filters, int $viewerId, int $limit, int $offset): array
    {
        [$where, $params] = $this->filters($filters);
        $params[':viewer'] = $viewerId;
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->pdo->prepare(
            self::SELECT . $where . ' ORDER BY r.created_at DESC, r.id DESC LIMIT :limit OFFSET :offset'
        );
        $this->execute($stmt, $params);
        return $stmt->fetchAll();
    }

    public function count(array $filters): int
    {
        [$where, $params] = $this->filters($filters);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM recipes r' . $where);
        $this->execute($stmt, $params);
        return (int) $stmt->fetchColumn();
    }

    public function find(int $id, int $viewerId): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE r.id = :id');
        $this->execute($stmt, [':viewer' => $viewerId, ':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM recipes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function ingredients(int $recipeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT quantity, unit, name FROM ingredients WHERE recipe_id = :recipe ORDER BY position'
        );
        $stmt->execute([':recipe' => $recipeId]);
        return $stmt->fetchAll();
    }

    /** Inserts the recipe and all its ingredients together, or nothing at all. */
    public function create(int $userId, array $data): int
    {
        return $this->transaction(function () use ($userId, $data): int {
            $stmt = $this->pdo->prepare(
                'INSERT INTO recipes (user_id, category_id, title, description, servings, steps)
                 VALUES (:user, :category, :title, :description, :servings, :steps)'
            );
            $stmt->execute([
                ':user'        => $userId,
                ':category'    => $data['category_id'],
                ':title'       => $data['title'],
                ':description' => $data['description'],
                ':servings'    => $data['servings'],
                ':steps'       => $data['steps'],
            ]);
            $recipeId = (int) $this->pdo->lastInsertId();

            $this->insertIngredients($recipeId, $data['ingredients']);
            return $recipeId;
        });
    }

    /**
     * Updates the recipe and replaces its ingredient list in one transaction.
     * Returns false if the recipe doesn't exist or isn't owned by $userId.
     */
    public function update(int $id, int $userId, array $data): bool
    {
        return $this->transaction(function () use ($id, $userId, $data): bool {
            // Lock the row and confirm ownership first. (rowCount() on the UPDATE can't tell
            // "not yours" apart from "saved twice in the same second with no changes".)
            $owner = $this->pdo->prepare('SELECT 1 FROM recipes WHERE id = :id AND user_id = :user FOR UPDATE');
            $owner->execute([':id' => $id, ':user' => $userId]);
            if (!$owner->fetchColumn()) {
                return false;
            }

            $stmt = $this->pdo->prepare(
                'UPDATE recipes
                 SET category_id = :category, title = :title, description = :description,
                     servings = :servings, steps = :steps, updated_at = NOW()
                 WHERE id = :id AND user_id = :user'
            );
            $stmt->execute([
                ':category'    => $data['category_id'],
                ':title'       => $data['title'],
                ':description' => $data['description'],
                ':servings'    => $data['servings'],
                ':steps'       => $data['steps'],
                ':id'          => $id,
                ':user'        => $userId,
            ]);

            $this->pdo->prepare('DELETE FROM ingredients WHERE recipe_id = :recipe')->execute([':recipe' => $id]);
            $this->insertIngredients($id, $data['ingredients']);
            return true;
        });
    }

    /** Ingredients, comments and favorites go with it through ON DELETE CASCADE. */
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM recipes WHERE id = :id AND user_id = :user');
        $stmt->execute([':id' => $id, ':user' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------------------------

    private function insertIngredients(int $recipeId, array $ingredients): void
    {
        // One prepared statement, executed once per row.
        $stmt = $this->pdo->prepare(
            'INSERT INTO ingredients (recipe_id, position, quantity, unit, name)
             VALUES (:recipe, :position, :quantity, :unit, :name)'
        );
        foreach (array_values($ingredients) as $position => $ingredient) {
            $stmt->execute([
                ':recipe'   => $recipeId,
                ':position' => $position + 1,
                ':quantity' => $ingredient['quantity'],
                ':unit'     => $ingredient['unit'],
                ':name'     => $ingredient['name'],
            ]);
        }
    }

    /**
     * Turns the search filters into a WHERE clause with named placeholders.
     * User text only ever goes into $params, never into the SQL string.
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function filters(array $filters): array
    {
        $conditions = [];
        $params = [];

        // Every word must appear in the title, description or an ingredient name.
        $words = preg_split('/\s+/u', trim($filters['q'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        foreach (array_slice($words, 0, 5) as $i => $word) {
            // Escape LIKE wildcards so "%" or "_" in a search are matched literally.
            $like = '%' . addcslashes($word, '%_\\') . '%';
            $conditions[] = "(r.title LIKE :q{$i}a OR r.description LIKE :q{$i}b
                OR EXISTS (SELECT 1 FROM ingredients i WHERE i.recipe_id = r.id AND i.name LIKE :q{$i}c))";
            $params[":q{$i}a"] = $like;
            $params[":q{$i}b"] = $like;
            $params[":q{$i}c"] = $like;
        }

        if (!empty($filters['category'])) {
            $conditions[] = 'r.category_id = :category';
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['author'])) {
            $conditions[] = 'r.user_id = :author';
            $params[':author'] = $filters['author'];
        }

        if (!empty($filters['favorites_of'])) {
            $conditions[] = 'EXISTS (SELECT 1 FROM favorites ff WHERE ff.recipe_id = r.id AND ff.user_id = :fan)';
            $params[':fan'] = $filters['favorites_of'];
        }

        return [$conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', $params];
    }

    /** bindValue with the right type so LIMIT/OFFSET get real integers. */
    private function execute(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
    }

    private function transaction(callable $work): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $work();
            // A false result (e.g. not the owner) means nothing should be saved.
            $result === false ? $this->pdo->rollBack() : $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
