<?php
include_once "./utils/string.php";
function ReadFullFile(string $path):string
{
    if (isEmpty($path))
    {
        throw new InvalidArgumentException("path can't be null or empty!");
    }
    $file = fopen($path, "r") or throw new RuntimeException('Impossible to open the file!');
    $content = fread($file, filesize($path));
    fclose($file);
    return $content;
}