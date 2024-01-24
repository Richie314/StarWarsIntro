<?php
include_once "./utils/string.php";
function ReadFullFile($path)
{
    if (isEmpty($path))
    {
        throw new InvalidArgumentException("path can't be null or empty!", 500);
    }
    $file = fopen($path, "r");
    if (!$file)
    {
        throw new RuntimeException("Impossible to open the file '$path'.", 404);
    }
    $content = fread($file, filesize($path));
    fclose($file);
    return remove_utf8_bom($content);
}
function remove_utf8_bom($text)
{
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}