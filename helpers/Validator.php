<?php
declare(strict_types=1);

class Validator
{
    public static function email(?string $value): bool
    {
        if ($value === null || trim($value) === '') {
            return true;
        }
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function positiveInt(mixed $value, int $min = 1): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        return filter_var($value, FILTER_VALIDATE_INT) !== false && (int)$value >= $min;
    }

    public static function maxLen(?string $value, int $len): bool
    {
        if ($value === null) {
            return true;
        }
        return mb_strlen(trim($value)) <= $len;
    }

    public static function phone(?string $value): bool
    {
        if ($value === null || trim($value) === '') {
            return true;
        }

        $v = trim($value);
        if (preg_match('/^00\d{6,20}$/', $v) === 1) {
            return true;
        }

        return preg_match('/^[0-9\+\-\s\(\)]{6,25}$/', $v) === 1;
    }
}
