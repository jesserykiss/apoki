<?php

declare(strict_types=1);

namespace Apoki\Services;

use Apoki\Core\NotFoundException;
use Apoki\Core\ValidationException;
use Apoki\Core\Validator;
use Apoki\DAO\BanquetDao;
use Apoki\DAO\BanquetTypeDao;
use Apoki\DAO\GiftRecordDao;
use Apoki\DAO\RelativeDao;
use DomainException;

final class BanquetService
{
    public function __construct(
        private readonly BanquetDao $banquetDao,
        private readonly BanquetTypeDao $typeDao,
        private readonly RelativeDao $relativeDao,
        private readonly GiftRecordDao $giftRecordDao
    ) {
    }

    public function paginate(int $userId, array $filters, int $page, int $perPage = 20): array
    {
        return $this->banquetDao->paginate($userId, $filters, $page, $perPage);
    }

    public function find(int $userId, int $id): ?array
    {
        return $this->banquetDao->findById($userId, $id);
    }

    public function formData(int $userId, ?int $id = null): array
    {
        $banquet = $id === null ? null : $this->banquetDao->findById($userId, $id);

        return [
            'types' => $this->typeDao->allSystem(),
            'relatives' => $this->relativeDao->allActive($userId),
            'banquet' => $banquet,
            'has_gifts' => $banquet !== null && $this->giftRecordDao->countActiveByBanquet($userId, $id) > 0,
        ];
    }

    public function types(): array
    {
        return $this->typeDao->allSystem();
    }

    public function create(int $userId, array $data): int
    {
        $normalized = $this->validateAndNormalize($userId, $data);
        return $this->banquetDao->create($userId, $normalized);
    }

    public function update(int $userId, int $id, array $data): void
    {
        $existing = $this->banquetDao->findById($userId, $id);
        if ($existing === null) {
            throw new NotFoundException('宴请不存在。');
        }

        $normalized = $this->validateAndNormalize($userId, $data);
        $hasGifts = $this->giftRecordDao->countActiveByBanquet($userId, $id) > 0;
        $hostChanged = (int) $existing['host_type'] !== (int) $normalized['host_type']
            || (int) ($existing['host_relative_id'] ?? 0) !== (int) ($normalized['host_relative_id'] ?? 0);

        if ($hasGifts && $hostChanged) {
            throw new DomainException('该宴请已有礼金流水，不能修改主办类型或主办亲戚。');
        }

        $this->banquetDao->update($userId, $id, $normalized);
    }

    public function delete(int $userId, int $id): void
    {
        if ($this->banquetDao->findById($userId, $id) === null) {
            throw new NotFoundException('宴请不存在。');
        }

        $giftCount = $this->giftRecordDao->countActiveByBanquet($userId, $id);
        if ($giftCount > 0) {
            throw new DomainException('该宴请仍有 ' . $giftCount . ' 条礼金记录，请先处理后再删除。');
        }

        $this->banquetDao->softDelete($userId, $id);
    }

    private function validateAndNormalize(int $userId, array $data): array
    {
        $errors = Validator::validate($data, [
            'type_id' => ['required', 'int'],
            'host_type' => ['required', 'in:1,2'],
            'title' => ['required', 'max:100'],
            'banquet_date' => ['required', 'date'],
            'location' => ['max:200'],
            'remark' => ['max:500'],
        ]);

        $typeId = (int) ($data['type_id'] ?? 0);
        if ($this->typeDao->findSystemById($typeId) === null) {
            $errors['type_id'] = '宴请类型不正确。';
        }

        $hostType = (string) ($data['host_type'] ?? '');
        $hostRelativeId = (int) ($data['host_relative_id'] ?? 0);
        if ($hostType === '2' && $this->relativeDao->findById($userId, $hostRelativeId) === null) {
            $errors['host_relative_id'] = '请选择有效的亲戚。';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'type_id' => $typeId,
            'host_type' => $hostType === '1' ? 1 : 2,
            'host_relative_id' => $hostType === '1' ? null : $hostRelativeId,
            'title' => trim((string) $data['title']),
            'banquet_date' => (string) $data['banquet_date'],
            'location' => trim((string) ($data['location'] ?? '')),
            'remark' => trim((string) ($data['remark'] ?? '')),
        ];
    }
}
