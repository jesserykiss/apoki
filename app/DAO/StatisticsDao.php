<?php

declare(strict_types=1);

namespace Apoki\DAO;

use PDO;

final class StatisticsDao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function summary(int $userId, array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($userId, $filters);
        $whereSql = implode(' AND ', $where);

        $statement = $this->pdo->prepare(
            'SELECT COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE 0 END), 0) AS total_received,
                    COALESCE(SUM(CASE WHEN g.direction = 2 THEN g.amount ELSE 0 END), 0) AS total_sent,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE -g.amount END), 0) AS net,
                    COUNT(g.id) AS record_count
             FROM gift_records g
             JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id AND b.is_deleted = 0
             WHERE ' . $whereSql
        );
        $statement->execute($params);

        return $statement->fetch() ?: [
            'total_received' => '0',
            'total_sent' => '0',
            'net' => '0',
            'record_count' => 0,
        ];
    }

    public function topRelatives(int $userId, int $limit = 10): array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.id, r.name,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE 0 END), 0) AS total_received,
                    COALESCE(SUM(CASE WHEN g.direction = 2 THEN g.amount ELSE 0 END), 0) AS total_sent,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE -g.amount END), 0) AS net
             FROM gift_records g
             JOIN relatives r ON r.user_id = g.user_id AND r.id = g.relative_id AND r.is_deleted = 0
             WHERE g.user_id = :user_id AND g.is_deleted = 0
             GROUP BY r.id, r.name
             ORDER BY ABS(COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE -g.amount END), 0)) DESC, r.name
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function recentGifts(int $userId, int $limit = 8): array
    {
        $statement = $this->pdo->prepare(
            'SELECT g.id, g.direction, g.amount, g.gift_type, g.gift_desc, g.gift_date,
                    r.name AS relative_name, b.title AS banquet_title, t.name AS banquet_type_name
             FROM gift_records g
             JOIN relatives r ON r.user_id = g.user_id AND r.id = g.relative_id
             JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id
             JOIN banquet_types t ON t.id = b.type_id
             WHERE g.user_id = :user_id AND g.is_deleted = 0
             ORDER BY g.gift_date DESC, g.id DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function byRelative(int $userId, array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($userId, $filters);
        $whereSql = implode(' AND ', $where);

        $statement = $this->pdo->prepare(
            'SELECT r.id, r.name,
                    COUNT(g.id) AS record_count,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE 0 END), 0) AS total_received,
                    COALESCE(SUM(CASE WHEN g.direction = 2 THEN g.amount ELSE 0 END), 0) AS total_sent,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE -g.amount END), 0) AS net
             FROM gift_records g
             JOIN relatives r ON r.user_id = g.user_id AND r.id = g.relative_id AND r.is_deleted = 0
             JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id AND b.is_deleted = 0
             WHERE ' . $whereSql . '
             GROUP BY r.id, r.name
             ORDER BY ABS(COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE -g.amount END), 0)) DESC, r.name'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function byType(int $userId, array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($userId, $filters);
        $whereSql = implode(' AND ', $where);

        $statement = $this->pdo->prepare(
            'SELECT t.id, t.code, t.name,
                    COUNT(g.id) AS record_count,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE 0 END), 0) AS total_received,
                    COALESCE(SUM(CASE WHEN g.direction = 2 THEN g.amount ELSE 0 END), 0) AS total_sent,
                    COALESCE(SUM(CASE WHEN g.direction = 1 THEN g.amount ELSE -g.amount END), 0) AS net
             FROM gift_records g
             JOIN banquets b ON b.user_id = g.user_id AND b.id = g.banquet_id AND b.is_deleted = 0
             JOIN banquet_types t ON t.id = b.type_id
             WHERE ' . $whereSql . '
             GROUP BY t.id, t.code, t.name, t.sort
             ORDER BY t.sort, t.id'
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function buildWhere(int $userId, array $filters): array
    {
        $where = ['g.user_id = :user_id', 'g.is_deleted = 0'];
        $params = ['user_id' => $userId];

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
        if (!empty($filters['type_id'])) {
            $where[] = 'b.type_id = :type_id';
            $params['type_id'] = $filters['type_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'g.gift_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'g.gift_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        return [$where, $params];
    }
}
