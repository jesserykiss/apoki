<?php

declare(strict_types=1);

namespace Apoki\DAO;

use PDO;

final class GiftRecordDao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findById(int $userId, int $id, bool $withDeleted = false): ?array
    {
        $sql = 'SELECT g.id, g.user_id, g.banquet_id, g.relative_id, g.direction,
                       g.amount, g.gift_type, g.gift_desc, g.gift_date, g.remark, g.is_deleted,
                       r.name AS relative_name,
                       b.title AS banquet_title, b.host_type, b.host_relative_id, b.type_id,
                       t.name AS banquet_type_name
                FROM gift_records g
                JOIN relatives r ON r.user_id = g.user_id AND r.id = g.relative_id
                JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id
                JOIN banquet_types t ON t.id = b.type_id
                WHERE g.user_id = :user_id AND g.id = :id';
        if (!$withDeleted) {
            $sql .= ' AND g.is_deleted = 0';
        }
        $sql .= ' LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['user_id' => $userId, 'id' => $id]);
        $gift = $statement->fetch();

        return $gift === false ? null : $gift;
    }

    public function paginate(int $userId, array $filters, int $page, int $perPage): array
    {
        $where = ['g.user_id = :user_id', 'g.is_deleted = 0'];
        $params = ['user_id' => $userId];
        $this->applyFilters($where, $params, $filters);

        $whereSql = implode(' AND ', $where);
        $countSql = 'SELECT COUNT(*)
                     FROM gift_records g
                     JOIN relatives r ON r.user_id = g.user_id AND r.id = g.relative_id
                     JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id
                     JOIN banquet_types t ON t.id = b.type_id
                     WHERE ' . $whereSql;
        $listSql = 'SELECT g.id, g.banquet_id, g.relative_id, g.direction, g.amount,
                           g.gift_type, g.gift_desc, g.gift_date, g.remark,
                           r.name AS relative_name,
                           b.title AS banquet_title, b.host_type, b.type_id,
                           t.name AS banquet_type_name
                    FROM gift_records g
                    JOIN relatives r ON r.user_id = g.user_id AND r.id = g.relative_id
                    JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id
                    JOIN banquet_types t ON t.id = b.type_id
                    WHERE ' . $whereSql . '
                    ORDER BY g.gift_date DESC, g.id DESC
                    LIMIT :limit OFFSET :offset';

        $countStatement = $this->pdo->prepare($countSql);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $page = max(1, $page);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare($listSql);
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->bindValue('limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO gift_records
                (user_id, banquet_id, relative_id, direction, amount, gift_type, gift_desc, gift_date, remark)
             VALUES
                (:user_id, :banquet_id, :relative_id, :direction, :amount, :gift_type, :gift_desc, :gift_date, :remark)'
        );
        $statement->execute([
            'user_id' => $userId,
            'banquet_id' => $data['banquet_id'],
            'relative_id' => $data['relative_id'],
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'gift_type' => $data['gift_type'],
            'gift_desc' => $data['gift_desc'] ?: null,
            'gift_date' => $data['gift_date'],
            'remark' => $data['remark'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $userId, int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE gift_records
             SET banquet_id = :banquet_id,
                 relative_id = :relative_id,
                 direction = :direction,
                 amount = :amount,
                 gift_type = :gift_type,
                 gift_desc = :gift_desc,
                 gift_date = :gift_date,
                 remark = :remark
             WHERE user_id = :user_id AND id = :id AND is_deleted = 0'
        );
        $statement->execute([
            'banquet_id' => $data['banquet_id'],
            'relative_id' => $data['relative_id'],
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'gift_type' => $data['gift_type'],
            'gift_desc' => $data['gift_desc'] ?: null,
            'gift_date' => $data['gift_date'],
            'remark' => $data['remark'] ?: null,
            'user_id' => $userId,
            'id' => $id,
        ]);
    }

    public function softDelete(int $userId, int $id): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE gift_records SET is_deleted = 1
             WHERE user_id = :user_id AND id = :id AND is_deleted = 0'
        );
        $statement->execute(['user_id' => $userId, 'id' => $id]);

        return $statement->rowCount() > 0;
    }

    public function countActiveByRelative(int $userId, int $relativeId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM gift_records
             WHERE user_id = :user_id AND relative_id = :relative_id AND is_deleted = 0'
        );
        $statement->execute(['user_id' => $userId, 'relative_id' => $relativeId]);

        return (int) $statement->fetchColumn();
    }

    public function countActiveByBanquet(int $userId, int $banquetId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM gift_records
             WHERE user_id = :user_id AND banquet_id = :banquet_id AND is_deleted = 0'
        );
        $statement->execute(['user_id' => $userId, 'banquet_id' => $banquetId]);

        return (int) $statement->fetchColumn();
    }

    public function countActiveHostBanquets(int $userId, int $relativeId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM banquets
             WHERE user_id = :user_id AND host_relative_id = :relative_id AND is_deleted = 0'
        );
        $statement->execute(['user_id' => $userId, 'relative_id' => $relativeId]);

        return (int) $statement->fetchColumn();
    }

    private function applyFilters(array &$where, array &$params, array $filters): void
    {
        if (!empty($filters['direction'])) {
            $where[] = 'g.direction = :direction';
            $params['direction'] = $filters['direction'];
        }
        if (!empty($filters['relative_id'])) {
            $where[] = 'g.relative_id = :relative_id';
            $params['relative_id'] = $filters['relative_id'];
        }
        if (!empty($filters['banquet_id'])) {
            $where[] = 'g.banquet_id = :banquet_id';
            $params['banquet_id'] = $filters['banquet_id'];
        }
        if (!empty($filters['gift_type'])) {
            $where[] = 'g.gift_type = :gift_type';
            $params['gift_type'] = $filters['gift_type'];
        }
        if (!empty($filters['type_id'])) {
            $where[] = 'b.type_id = :type_id';
            $params['type_id'] = $filters['type_id'];
        }
        if (!empty($filters['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['date_from']) === 1) {
            $where[] = 'g.gift_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['date_to']) === 1) {
            $where[] = 'g.gift_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['keyword'])) {
            $where[] = '(g.gift_desc LIKE :keyword OR g.remark LIKE :keyword OR r.name LIKE :keyword OR b.title LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }
    }
}
