<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';

use StarWars\Support\I18n;

$en = json_decode(file_get_contents(__DIR__ . '/../app/Translations/en.json'), true, flags: JSON_THROW_ON_ERROR);
$it = json_decode(file_get_contents(__DIR__ . '/../app/Translations/it.json'), true, flags: JSON_THROW_ON_ERROR);
if (array_keys($en) !== array_keys($it) || count(array_filter($it, fn($text) => !is_string($text) || $text === ''))) {
    throw new RuntimeException('Translation catalogues must have matching keys and nonempty strings.');
}
foreach (['en', 'it', 'fr', null, ['it']] as $cookie) {
    $_COOKIE['site-language'] = $cookie;
    I18n::initialize();
    $locale = $cookie === 'it' ? 'it' : 'en';
    if (I18n::locale() !== $locale || I18n::text('My intros') !== ($locale === 'it' ? 'Le mie intro' : 'My intros')) {
        throw new RuntimeException('Language selection must default to English and accept only en/it.');
    }
    $title = I18n::html('Watch {title} (new tab)', ['title' => '<script>"&']);
    if (str_contains($title, '<script>') || !str_contains($title, '&lt;script&gt;')) {
        throw new RuntimeException('Translated parameters must be escaped in HTML.');
    }
    if (count(I18n::clientMessages()) !== 5) {
        throw new RuntimeException('Browser messages must use the selected catalogue.');
    }
}
echo "English/Italian catalogue parity, language fallback and safe interpolation passed.\n";
