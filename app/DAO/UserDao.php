<?php

declare(strict_types=1);

namespace Apoki\DAO;

use PDO;

final class UserDao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, username, password_hash, nickname, phone, email, status, updated_at
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, username, password_hash, nickname, phone, email, status, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, nickname, phone, email, status)
             VALUES (:username, :password_hash, :nickname, :phone, :email, 1)'
        );
        $statement->execute([
            'username' => $data['username'],
            'password_hash' => $data['password_hash'],
            'nickname' => $data['nickname'],
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id = :id'
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'id' => $id,
        ]);
    }

    public function findUpdatedAt(int $id): ?string
    {
        $statement = $this->pdo->prepare(
            'SELECT updated_at FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $updatedAt = $statement->fetchColumn();

        return $updatedAt === false ? null : (string) $updatedAt;
    }
}
