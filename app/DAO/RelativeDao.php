<?php

declare(strict_types=1);

namespace Apoki\DAO;

use PDO;

final class RelativeDao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findById(int $userId, int $id, bool $withDeleted = false): ?array
    {
        $sql = 'SELECT id, user_id, name, relation, family_side, category, gender, phone, remark, is_deleted
                FROM relatives
                WHERE user_id = :user_id AND id = :id';
        if (!$withDeleted) {
            $sql .= ' AND is_deleted = 0';
        }
        $sql .= ' LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['user_id' => $userId, 'id' => $id]);
        $relative = $statement->fetch();

        return $relative === false ? null : $relative;
    }

    public function paginate(int $userId, array $filters, int $page, int $perPage): array
    {
        $where = ['user_id = :user_id', 'is_deleted = 0'];
        $params = ['user_id' => $userId];
        $this->applyFilters($where, $params, $filters);

        $whereSql = implode(' AND ', $where);
        $countSql = 'SELECT COUNT(*) FROM relatives WHERE ' . $whereSql;
        $listSql = 'SELECT id, name, relation, family_side, category, gender, phone, remark
                    FROM relatives
                    WHERE ' . $whereSql . '
                    ORDER BY id DESC
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
            'SELECT id, name, relation, family_side, category, gender
             FROM relatives
             WHERE user_id = :user_id AND is_deleted = 0
             ORDER BY name, id'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO relatives
                (user_id, name, relation, family_side, category, gender, phone, remark)
             VALUES
                (:user_id, :name, :relation, :family_side, :category, :gender, :phone, :remark)'
        );
        $statement->execute([
            'user_id' => $userId,
            'name' => $data['name'],
            'relation' => $data['relation'],
            'family_side' => $data['family_side'],
            'category' => $data['category'],
            'gender' => $data['gender'],
            'phone' => $data['phone'] ?: null,
            'remark' => $data['remark'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $userId, int $id, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE relatives
             SET name = :name,
                 relation = :relation,
                 family_side = :family_side,
                 category = :category,
                 gender = :gender,
                 phone = :phone,
                 remark = :remark
             WHERE user_id = :user_id AND id = :id AND is_deleted = 0'
        );
        $statement->execute([
            'name' => $data['name'],
            'relation' => $data['relation'],
            'family_side' => $data['family_side'],
            'category' => $data['category'],
            'gender' => $data['gender'],
            'phone' => $data['phone'] ?: null,
            'remark' => $data['remark'] ?: null,
            'user_id' => $userId,
            'id' => $id,
        ]);
    }

    public function softDelete(int $userId, int $id): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE relatives SET is_deleted = 1
             WHERE user_id = :user_id AND id = :id AND is_deleted = 0'
        );
        $statement->execute(['user_id' => $userId, 'id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function applyFilters(array &$where, array &$params, array $filters): void
    {
        if (!empty($filters['keyword'])) {
            $where[] = '(name LIKE :keyword OR relation LIKE :keyword OR remark LIKE :keyword)';
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }
        if (!empty($filters['family_side'])) {
            $where[] = 'family_side = :family_side';
            $params['family_side'] = $filters['family_side'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'category = :category';
            $params['category'] = $filters['category'];
        }
    }
}
