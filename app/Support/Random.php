<?php
declare(strict_types=1);
namespace StarWars\Support;

use InvalidArgumentException;

final class Random
{
    private const PASSWORD_CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!|?=/&$-^';

    public static function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public static function float(float $min = 0, float $max = 1): float
    {
        if (!is_finite($min) || !is_finite($max) || $min > $max || !is_finite($max - $min)) {
            throw new InvalidArgumentException('Random bounds must be finite and ordered.');
        }
        // 53 random bits match the precision of a PHP double; both endpoints are included.
        $fraction = self::int(0, 9007199254740991) / 9007199254740991;
        return $min + ($max - $min) * $fraction;
    }

    public static function percentage(): string
    {
        return number_format(self::float(0, 100), 1, '.', '') . '%';
    }

    public static function password(int $length = 12): string
    {
        if ($length < 1) {
            throw new InvalidArgumentException('Password length must be positive.');
        }
        $password = '';
        $last = strlen(self::PASSWORD_CHARACTERS) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= self::PASSWORD_CHARACTERS[self::int(0, $last)];
        }
        return $password;
    }
}
