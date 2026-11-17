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

final class GiftRecordService
{
    public function __construct(
        private readonly GiftRecordDao $giftRecordDao,
        private readonly BanquetDao $banquetDao,
        private readonly RelativeDao $relativeDao,
        private readonly BanquetTypeDao $typeDao
    ) {
    }

    public function paginate(int $userId, array $filters, int $page, int $perPage = 20): array
    {
        return $this->giftRecordDao->paginate($userId, $filters, $page, $perPage);
    }

    public function find(int $userId, int $id): ?array
    {
        return $this->giftRecordDao->findById($userId, $id);
    }

    public function formData(int $userId, ?int $id = null, int $preselectedBanquetId = 0): array
    {
        $gift = $id === null ? null : $this->giftRecordDao->findById($userId, $id);

        return [
            'relatives' => $this->relativeDao->allActive($userId),
            'banquets' => $this->banquetDao->allActive($userId),
            'types' => $this->typeDao->allSystem(),
            'gift' => $gift,
            'preselected_banquet_id' => $preselectedBanquetId,
        ];
    }

    public function create(int $userId, array $data): int
    {
        $normalized = $this->validateAndNormalize($userId, $data);
        return $this->giftRecordDao->create($userId, $normalized);
    }

    public function update(int $userId, int $id, array $data): void
    {
        if ($this->giftRecordDao->findById($userId, $id) === null) {
            throw new NotFoundException('礼金记录不存在。');
        }

        $normalized = $this->validateAndNormalize($userId, $data);
        $this->giftRecordDao->update($userId, $id, $normalized);
    }

    public function delete(int $userId, int $id): void
    {
        if ($this->giftRecordDao->findById($userId, $id) === null) {
            throw new NotFoundException('礼金记录不存在。');
        }

        $this->giftRecordDao->softDelete($userId, $id);
    }

    private function validateAndNormalize(int $userId, array $data): array
    {
        $errors = Validator::validate($data, [
            'banquet_id' => ['required', 'int'],
            'relative_id' => ['required', 'int'],
            'direction' => ['required', 'in:1,2'],
            'amount' => ['required', 'amount'],
            'gift_type' => ['required', 'in:1,2,3'],
            'gift_desc' => ['max:200'],
            'gift_date' => ['required', 'date'],
            'remark' => ['max:500'],
        ]);

        $banquetId = (int) ($data['banquet_id'] ?? 0);
        $relativeId = (int) ($data['relative_id'] ?? 0);
        $direction = (int) ($data['direction'] ?? 0);
        $giftType = (int) ($data['gift_type'] ?? 0);

        $banquet = $this->banquetDao->findById($userId, $banquetId);
        if ($banquet === null) {
            $errors['banquet_id'] = '宴请不存在。';
        } else {
            $expectedDirection = (int) $banquet['host_type'] === 1 ? 1 : 2;
            if ($direction !== $expectedDirection) {
                $errors['direction'] = $expectedDirection === 1 ? '自己办的宴请只能收礼。' : '亲戚办的宴请只能送礼。';
            }

            if ((int) $banquet['host_type'] === 2 && (int) $banquet['host_relative_id'] !== $relativeId) {
                $errors['relative_id'] = '亲戚办宴请的礼金对象必须是主办亲戚。';
            }
        }

        if ($this->relativeDao->findById($userId, $relativeId) === null) {
            $errors['relative_id'] = '亲戚不存在。';
        }

        if ($giftType === 3 && trim((string) ($data['gift_desc'] ?? '')) === '') {
            $errors['gift_desc'] = '礼品类型必须填写礼品说明。';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'banquet_id' => $banquetId,
            'relative_id' => $relativeId,
            'direction' => $direction,
            'amount' => (string) $data['amount'],
            'gift_type' => $giftType,
            'gift_desc' => trim((string) ($data['gift_desc'] ?? '')),
            'gift_date' => (string) $data['gift_date'],
            'remark' => trim((string) ($data['remark'] ?? '')),
        ];
    }
}
