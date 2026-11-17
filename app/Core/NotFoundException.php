<?php

declare(strict_types=1);

namespace Apoki\Core;

use RuntimeException;

final class NotFoundException extends RuntimeException
{
    public function __construct(string $message = '请求的资源不存在。')
    {
        parent::__construct($message);
    }
}
