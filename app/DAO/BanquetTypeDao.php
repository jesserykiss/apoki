<?php

declare(strict_types=1);

namespace Apoki\DAO;

use PDO;

final class BanquetTypeDao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function allSystem(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, code, name, sort
             FROM banquet_types
             WHERE is_system = 1 AND user_id IS NULL
             ORDER BY sort, id'
        );

        return $statement->fetchAll();
    }

    public function findSystemById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, code, name, sort
             FROM banquet_types
             WHERE id = :id AND is_system = 1 AND user_id IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $type = $statement->fetch();

        return $type === false ? null : $type;
    }
}
