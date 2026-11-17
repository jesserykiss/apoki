<?php

declare(strict_types=1);

namespace Apoki\DAO;

use PDO;

final class BanquetDao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findById(int $userId, int $id, bool $withDeleted = false): ?array
    {
        $sql = 'SELECT b.id, b.user_id, b.type_id, b.host_type, b.host_relative_id,
                       b.title, b.banquet_date, b.location, b.remark, b.is_deleted,
                       t.name AS type_name,
                       hr.name AS host_relative_name
                FROM banquets b
                JOIN banquet_types t ON t.id = b.type_id
                LEFT JOIN relatives hr ON hr.user_id = b.user_id AND hr.id = b.host_relative_id
                WHERE b.user_id = :user_id AND b.id = :id';
        if (!$withDeleted) {
            $sql .= ' AND b.is_deleted = 0';
        }
        $sql .= ' LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['user_id' => $userId, 'id' => $id]);
        $banquet = $statement->fetch();

        return $banquet === false ? null : $banquet;
    }

    public function paginate(int $userId, array $filters, int $page, int $perPage): array
    {
        $where = ['b.user_id = :user_id', 'b.is_deleted = 0'];
        $params = ['user_id' => $userId];
        $this->applyFilters($where, $params, $filters);

        $whereSql = implode(' AND ', $where);
        $countSql = 'SELECT COUNT(*)
                     FROM banquets b
                     LEFT JOIN relatives hr ON hr.user_id = b.user_id AND hr.id = b.host_relative_id
                     WHERE ' . $whereSql;
        $listSql = 'SELECT b.id, b.type_id, b.host_type, b.host_relative_id, b.title,
                           b.banquet_date, b.location, b.remark,
                           t.name AS type_name, hr.name AS host_relative_name
                    FROM banquets b
                    JOIN banquet_types t ON t.id = b.type_id
                    LEFT JOIN relatives hr ON hr.user_id = b.user_id AND hr.id = b.host_relative_id
                    WHERE ' . $whereSql . '
                    ORDER BY b.banquet_date DESC, b.id DESC
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

    public function allActive(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT b.id, b.type_id, b.host_type, b.host_relative_id, b.title, b.banquet_date,
                    t.name AS type_name, hr.name AS host_relative_name
             FROM banquets b
             JOIN banquet_types t ON t.id = b.type_id
             LEFT JOIN relatives hr ON hr.user_id = b.user_id AND hr.id = b.host_relative_id
             WHERE b.user_id = :user_id AND b.is_deleted = 0
             ORDER BY b.banquet_date DESC, b.id DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO banquets
                (user_id, type_id, host_type, host_relative_id, title, banquet_date, location, remark)
             VALUES
                (:user_id, :type_id, :host_type, :host_relative_id, :title, :banquet_date, :location, :remark)'
        );
        $statement->execute([
            'user_id' => $userId,
            'type_id' => $data['type_id'],
            'host_type' => $data['host_type'],
            'host_relative_id' => $data['host_relative_id'] ?: null,
            'title' => $data['title'],
            'banquet_date' => $data['banquet_date'],
            'location' => $data['location'] ?: null,
            'remark' => $data['remark'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $userId, int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE banquets
             SET type_id = :type_id,
                 host_type = :host_type,
                 host_relative_id = :host_relative_id,
                 title = :title,
                 banquet_date = :banquet_date,
                 location = :location,
                 remark = :remark
             WHERE user_id = :user_id AND id = :id AND is_deleted = 0'
        );
        $statement->execute([
            'type_id' => $data['type_id'],
            'host_type' => $data['host_type'],
            'host_relative_id' => $data['host_relative_id'] ?: null,
            'title' => $data['title'],
            'banquet_date' => $data['banquet_date'],
            'location' => $data['location'] ?: null,
            'remark' => $data['remark'] ?: null,
            'user_id' => $userId,
            'id' => $id,
        ]);
    }

    public function softDelete(int $userId, int $id): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE banquets SET is_deleted = 1
             WHERE user_id = :user_id AND id = :id AND is_deleted = 0'
        );
        $statement->execute(['user_id' => $userId, 'id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function applyFilters(array &$where, array &$params, array $filters): void
    {
        if (!empty($filters['type_id'])) {
            $where[] = 'b.type_id = :type_id';
            $params['type_id'] = $filters['type_id'];
        }
        if (!empty($filters['host_type'])) {
            $where[] = 'b.host_type = :host_type';
            $params['host_type'] = $filters['host_type'];
        }
        if (!empty($filters['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['date_from']) === 1) {
            $where[] = 'b.banquet_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $filters['date_to']) === 1) {
            $where[] = 'b.banquet_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['keyword'])) {
            $where[] = '(b.title LIKE :keyword OR b.location LIKE :keyword OR b.remark LIKE :keyword OR hr.name LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }
    }
}
