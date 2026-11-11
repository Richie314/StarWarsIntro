<?php
declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';

use StarWars\Support\Random;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
for ($i = 0; $i < 1000; $i++) {
    $episode = Random::int(1, 9);
    check($episode >= 1 && $episode <= 9, 'Random integer outside its bounds.');
    $value = Random::float(-20, 40);
    check(is_finite($value) && $value >= -20 && $value <= 40, 'Random float outside its bounds.');
    check((bool)preg_match('/^(?:100\.0|\d{1,2}\.\d)%$/D', Random::percentage()), 'Invalid CSS percentage.');
}
check(Random::float(7, 7) === 7.0, 'Equal bounds must return that value.');
foreach ([1, 12, 64] as $length) {
    $password = Random::password($length);
    check(strlen($password) === $length && !preg_match('/[^\x21-\x7E]/', $password), 'Invalid generated password.');
}
foreach ([fn() => Random::float(3, 2), fn() => Random::float(NAN, 1), fn() => Random::float(0, INF),
    fn() => Random::float(-PHP_FLOAT_MAX, PHP_FLOAT_MAX), fn() => Random::password(0), fn() => Random::password(-1)] as $invalid) {
    try {
        $invalid();
        throw new RuntimeException('Invalid random input was accepted.');
    } catch (InvalidArgumentException) {
    }
}
echo "Random bounds, percentage formatting, password lengths and invalid inputs verified.\n";
