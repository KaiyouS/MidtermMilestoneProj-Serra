<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    /** Hashes the password here so a plain-text password can never reach the table. */
    public function create(string $displayName, string $email, string $plainPassword): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (display_name, email, password_hash) VALUES (:name, :email, :hash)'
        );
        $stmt->execute([
            ':name'  => $displayName,
            ':email' => $email,
            ':hash'  => password_hash($plainPassword, PASSWORD_DEFAULT),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Called after login when PHP's default hashing algorithm has been upgraded. */
    public function updatePasswordHash(int $id, string $plainPassword): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([':hash' => password_hash($plainPassword, PASSWORD_DEFAULT), ':id' => $id]);
    }
}
