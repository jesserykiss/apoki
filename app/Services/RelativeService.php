<?php

declare(strict_types=1);

namespace Apoki\Services;

use Apoki\Core\NotFoundException;
use Apoki\Core\ValidationException;
use Apoki\Core\Validator;
use Apoki\DAO\GiftRecordDao;
use Apoki\DAO\RelativeDao;
use DomainException;

final class RelativeService
{
    public function __construct(
        private readonly RelativeDao $relativeDao,
        private readonly GiftRecordDao $giftRecordDao
    ) {
    }

    public function paginate(int $userId, array $filters, int $page, int $perPage = 20): array
    {
        return $this->relativeDao->paginate($userId, $filters, $page, $perPage);
    }

    public function find(int $userId, int $id): ?array
    {
        return $this->relativeDao->findById($userId, $id);
    }

    public function allActive(int $userId): array
    {
        return $this->relativeDao->allActive($userId);
    }

    public function create(int $userId, array $data): int
    {
        $this->validate($data);
        return $this->relativeDao->create($userId, $this->normalize($data));
    }

    public function update(int $userId, int $id, array $data): void
    {
        if ($this->relativeDao->findById($userId, $id) === null) {
            throw new NotFoundException('亲戚不存在。');
        }

        $this->validate($data);
        $this->relativeDao->update($userId, $id, $this->normalize($data));
    }

    public function delete(int $userId, int $id): void
    {
        if ($this->relativeDao->findById($userId, $id) === null) {
            throw new NotFoundException('亲戚不存在。');
        }

        $giftCount = $this->giftRecordDao->countActiveByRelative($userId, $id);
        $banquetCount = $this->giftRecordDao->countActiveHostBanquets($userId, $id);
        if ($giftCount > 0 || $banquetCount > 0) {
            $relations = [];
            if ($giftCount > 0) {
                $relations[] = $giftCount . ' 条礼金';
            }
            if ($banquetCount > 0) {
                $relations[] = $banquetCount . ' 场宴请';
            }
            throw new DomainException('该亲戚仍有 ' . implode('和', $relations) . ' 关联，请先处理后再删除。');
        }

        $this->relativeDao->softDelete($userId, $id);
    }

    private function validate(array $data): void
    {
        $errors = Validator::validate($data, [
            'name' => ['required', 'max:50'],
            'relation' => ['required', 'max:30'],
            'family_side' => ['required', 'in:father,mother,spouse,other'],
            'category' => ['required', 'in:elder,peer,younger,other'],
            'gender' => ['required', 'in:male,female,unknown'],
            'phone' => ['max:30'],
            'remark' => ['max:500'],
        ]);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    private function normalize(array $data): array
    {
        return [
            'name' => trim((string) $data['name']),
            'relation' => trim((string) $data['relation']),
            'family_side' => (string) $data['family_side'],
            'category' => (string) $data['category'],
            'gender' => (string) $data['gender'],
            'phone' => trim((string) ($data['phone'] ?? '')),
            'remark' => trim((string) ($data['remark'] ?? '')),
        ];
    }
}
