<?php
declare(strict_types=1);
namespace StarWars\Support;

use Richie314\SimpleMvc\Utils\Cookie;

final class I18n
{
    private static string $locale = 'en';
    private static array $catalogues = [];

    public static function initialize(): void
    {
        $locale = Cookie::Get('site-language');
        self::$locale = in_array($locale, ['en', 'it'], true) ? $locale : 'en';
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function text(string $key, array $parameters = []): string
    {
        self::$catalogues[self::$locale] ??= json_decode(
            file_get_contents(__DIR__ . '/../Translations/' . self::$locale . '.json'),
            associative: true, flags: JSON_THROW_ON_ERROR,
        );
        $text = self::$catalogues[self::$locale][$key] ?? $key;
        $replace = [];
        foreach ($parameters as $name => $value) {
            $replace['{' . $name . '}'] = (string)$value;
        }
        return strtr($text, $replace);
    }

    public static function html(string $key, array $parameters = []): string
    {
        return htmlspecialchars(self::text($key, $parameters), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function clientMessages(): array
    {
        $keys = ['deleteFailed' => 'Could not delete. Please try again.', 'saved' => 'Changes saved successfully!',
            'shareTitle' => 'Custom Star Wars intro', 'shareText' => 'Watch my custom Star Wars intro!',
            'shareFailed' => 'Sharing failed. Please try again.'];
        return array_map(self::text(...), $keys);
    }
}
