<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';
            if (is_array($value)) {
                $value = '';
            }

            foreach ((array) $fieldRules as $rule) {
                [$name, $parameter] = array_pad(explode(':', (string) $rule, 2), 2, null);
                $error = self::check((string) $name, $value, $parameter);

                if ($error !== null) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }

        return $errors;
    }

    private static function check(string $rule, mixed $value, ?string $parameter): ?string
    {
        $stringValue = trim((string) $value);

        return match ($rule) {
            'required' => $stringValue === '' ? '此项为必填项。' : null,
            'max' => $stringValue !== '' && self::length($stringValue) > (int) $parameter ? '长度不能超过 ' . $parameter . ' 个字符。' : null,
            'min' => $stringValue !== '' && self::length($stringValue) < (int) $parameter ? '长度不能少于 ' . $parameter . ' 个字符。' : null,
            'regex' => $stringValue !== '' && preg_match((string) $parameter, $stringValue) !== 1 ? '格式不正确。' : null,
            'in' => $stringValue !== '' && !in_array($stringValue, explode(',', (string) $parameter), true) ? '选项不正确。' : null,
            'date' => $stringValue !== '' && self::isDate($stringValue) === false ? '日期格式不正确。' : null,
            'amount' => $stringValue !== '' && self::isAmount($stringValue) === false ? '金额必须大于 0 且最多保留两位小数。' : null,
            'email' => $stringValue !== '' && filter_var($stringValue, FILTER_VALIDATE_EMAIL) === false ? '邮箱格式不正确。' : null,
            'int' => $stringValue !== '' && filter_var($stringValue, FILTER_VALIDATE_INT) === false ? '必须为整数。' : null,
            default => null,
        };
    }

    private static function isDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private static function isAmount(string $value): bool
    {
        if (preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $value) !== 1) {
            return false;
        }

        if (function_exists('bccomp')) {
            return bccomp($value, '0', 2) > 0 && bccomp($value, '99999999.99', 2) <= 0;
        }

        return self::compareDecimal($value, '0') > 0
            && self::compareDecimal($value, '99999999.99') <= 0;
    }

    private static function compareDecimal(string $left, string $right): int
    {
        [$leftInteger, $leftDecimal] = array_pad(explode('.', $left, 2), 2, '0');
        [$rightInteger, $rightDecimal] = array_pad(explode('.', $right, 2), 2, '0');
        $leftInteger = ltrim($leftInteger, '0') ?: '0';
        $rightInteger = ltrim($rightInteger, '0') ?: '0';

        if (strlen($leftInteger) !== strlen($rightInteger)) {
            return strlen($leftInteger) <=> strlen($rightInteger);
        }

        $integerComparison = strcmp($leftInteger, $rightInteger);
        if ($integerComparison !== 0) {
            return $integerComparison <=> 0;
        }

        return strcmp(str_pad($leftDecimal, 2, '0'), str_pad($rightDecimal, 2, '0'));
    }

    private static function length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        return (int) preg_match_all('/./us', $value);
    }
}
