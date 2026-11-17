<?php

declare(strict_types=1);

namespace Apoki\Core;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public function __construct(private readonly array $errors)
    {
        parent::__construct('提交的数据未通过校验。');
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
