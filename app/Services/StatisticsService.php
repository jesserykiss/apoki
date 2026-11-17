<?php

declare(strict_types=1);

namespace Apoki\Services;

use Apoki\Core\Validator;
use Apoki\DAO\BanquetTypeDao;
use Apoki\DAO\RelativeDao;
use Apoki\DAO\StatisticsDao;

final class StatisticsService
{
    public function __construct(
        private readonly StatisticsDao $statisticsDao,
        private readonly RelativeDao $relativeDao,
        private readonly BanquetTypeDao $typeDao
    ) {
    }

    public function dashboard(int $userId): array
    {
        return [
            'summary' => $this->statisticsDao->summary($userId),
            'topRelatives' => $this->statisticsDao->topRelatives($userId, 10),
            'recentGifts' => $this->statisticsDao->recentGifts($userId, 8),
        ];
    }

    public function index(int $userId, array $filters): array
    {
        $filters = $this->normalizeFilters($filters);

        return [
            'filters' => $filters,
            'summary' => $this->statisticsDao->summary($userId, $filters),
            'byRelative' => $this->statisticsDao->byRelative($userId, $filters),
            'byType' => $this->statisticsDao->byType($userId, $filters),
            'relatives' => $this->relativeDao->allActive($userId),
            'types' => $this->typeDao->allSystem(),
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        $allowed = [
            'direction' => '',
            'relative_id' => '',
            'banquet_id' => '',
            'type_id' => '',
            'date_from' => '',
            'date_to' => '',
        ];
        $filters = array_intersect_key($filters, $allowed);
        $filters = array_merge($allowed, $filters);

        foreach (['direction', 'relative_id', 'banquet_id', 'type_id'] as $key) {
            $filters[$key] = filter_var($filters[$key], FILTER_VALIDATE_INT);
            if ($filters[$key] === false || $filters[$key] <= 0) {
                $filters[$key] = '';
            }
        }

        foreach (['date_from', 'date_to'] as $key) {
            $value = trim((string) $filters[$key]);
            $filters[$key] = $this->validDate($value) ? $value : '';
        }

        return $filters;
    }

    private function validDate(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        $errors = Validator::validate(['date' => $value], ['date' => ['date']]);
        return !isset($errors['date']);
    }
}
