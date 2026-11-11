<?php
// Hashes and newline counts were captured from the original .txt bytes before migration.
require dirname(__DIR__) . '/vendor/autoload.php';

use StarWars\Models\Opening;
use StarWars\Models\OpeningLanguage;

$originals = json_decode(file_get_contents(__DIR__ . '/preload-originals.json'), true, flags: JSON_THROW_ON_ERROR);
foreach ($originals as $key => $expected) {
    [$language, $episode] = explode('/', $key);
    $data = json_decode(file_get_contents(dirname(__DIR__) . "/preload/$key.json"), true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data['content']) || !array_is_list($data['content']) || $data['content'] === []) {
        throw new RuntimeException("Intro content must be a list of lines: $key");
    }
    foreach ($data['content'] as $line) {
        if (!is_string($line) || str_contains($line, "\n") || str_contains($line, "\r")) {
            throw new RuntimeException("Invalid intro line: $key");
        }
    }
    $opening = Opening::LoadOriginal((int)$episode, OpeningLanguage::from($language));
    $content = $opening->Content;
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        throw new RuntimeException("Unexpected UTF-8 BOM: $key");
    }
    // The BOM was deliberately removed; reconstruct it only to verify the captured original hash.
    $originalBytes = ($expected['bom'] ? "\xEF\xBB\xBF" : '') . $content;
    if (hash('sha256', $originalBytes) !== $expected['sha256']
        || strlen($originalBytes) !== $expected['bytes']
        || substr_count($content, "\r\n") !== $expected['crlf']
        || substr_count($content, "\n") !== $expected['lf']
        || $opening->Title !== $expected['title']
        || $opening->Episode !== $expected['episode']) {
        throw new RuntimeException("Original intro changed: $key");
    }
    if ($opening->Paragraphs() === []) {
        throw new RuntimeException("Missing intro paragraphs: $key");
    }
}
echo 'Verified all ' . count($originals) . " intros: text and line endings match the originals; BOMs are removed.\n";
