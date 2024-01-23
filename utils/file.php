<?php
include_once "./utils/string.php";
function ReadFullFile(string $path)
{
    if (isEmpty($path))
    {
        throw new InvalidArgumentException("path can't be null or empty!", 500);
    }
    $file = fopen($path, "r") or throw new RuntimeException("Impossible to open the file '$path'.", 404);
    $content = fread($file, filesize($path));
    fclose($file);
    return $content;
}