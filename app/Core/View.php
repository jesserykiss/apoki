<?php

declare(strict_types=1);

namespace Apoki\Core;

use RuntimeException;

final class View
{
    private const FAMILY_SIDE_LABELS = [
        'father' => '父方',
        'mother' => '母方',
        'spouse' => '配偶方',
		'brother' => '兄弟姐妹',
        'other' => '朋友',
    ];

    private const CATEGORY_LABELS = [
        'elder' => '长辈',
        'peer' => '平辈',
        'younger' => '晚辈',
        'other' => '其他',
    ];

    private const GENDER_LABELS = [
        'male' => '男',
        'female' => '女',
        'unknown' => '未填',
    ];

    public function render(string $template, array $data = []): string
    {
        $data['currentUser'] ??= (Auth::check() ? Auth::user() : null);
        $data['flashSuccess'] ??= Flash::get('flash_success');
        $data['flashError'] ??= Flash::get('flash_error');
        $oldInput = $data['oldInput'] ?? Flash::get('old_input', []);
        $data['oldInput'] = is_array($oldInput) ? $oldInput : [];
        $GLOBALS['viewOldInput'] = $data['oldInput'];
        $data['active'] ??= '';

        $layout = (string) ($data['layout'] ?? 'app');
        $data['content'] = $this->partial($template, $data);

        return $this->partial('layouts/' . $layout, $data);
    }

    public function partial(string $template, array $data = []): string
    {
        $template = str_replace(['\\', '..'], ['/', ''], $template);
        $file = VIEW_PATH . '/' . ltrim($template, '/') . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('模板不存在: ' . $template);
        }

        $view = $this;
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function csrfToken(): string
    {
        return Csrf::token();
    }

    public function old(string $key, mixed $default = ''): mixed
    {
        return $GLOBALS['viewOldInput'][$key] ?? $default;
    }

    public function selected(mixed $value, mixed $expected): string
    {
        return (string) $value === (string) $expected ? ' selected' : '';
    }

    public function checked(mixed $value, mixed $expected): string
    {
        return (string) $value === (string) $expected ? ' checked' : '';
    }

    public function active(string $value, string $expected): string
    {
        return $value === $expected ? ' active' : '';
    }

    public function money(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            $value = '0';
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');
        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $value, $matches)) {
            return '¥0.00';
        }

        $integer = $matches[1];
        $decimal = str_pad($matches[2] ?? '', 2, '0');
        $integer = ltrim($integer, '0');
        $integer = $integer === '' ? '0' : $integer;

        $length = strlen($integer);
        $groups = [];
        for ($index = $length; $index > 0; $index -= 3) {
            $groups[] = substr($integer, max(0, $index - 3), min(3, $index));
        }

        $formatted = implode(',', array_reverse($groups)) . '.' . $decimal;
        return ($negative ? '-¥' : '¥') . $formatted;
    }

    public function date(mixed $value, string $format = 'Y-m-d'): string
    {
        try {
            $date = new \DateTimeImmutable((string) $value);
            return $date->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function qs(array $params): string
    {
        $params = array_filter($params, static fn (mixed $value): bool => $value !== '' && $value !== null);
        return $params === [] ? '' : '?' . http_build_query($params);
    }

    public function familySide(string $value): string
    {
        return self::FAMILY_SIDE_LABELS[$value] ?? $value;
    }

    public function category(string $value): string
    {
        return self::CATEGORY_LABELS[$value] ?? $value;
    }

    public function gender(string $value): string
    {
        return self::GENDER_LABELS[$value] ?? $value;
    }

    public function hostType(string $value): string
    {
        return $value === '1' ? '自己办' : '亲戚办';
    }

    public function direction(string $value): string
    {
        return $value === '1' ? '收礼' : '送礼';
    }

    public function giftType(string $value): string
    {
        return match ($value) {
            '1' => '现金',
            '2' => '转账',
            '3' => '礼品',
            default => $value,
        };
    }

}
