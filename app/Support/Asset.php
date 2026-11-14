<?php
declare(strict_types=1);
namespace StarWars\Support;

final class Asset
{
    public static function url(string $path, string $prefix = ''): string
    {
        $hash = hash_file('sha256', dirname(__DIR__, 2) . '/assets/' . $path);
        return $prefix . '/assets/' . $path . '?v=' . $hash;
    }
}
