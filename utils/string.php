<?php
/**
 * Checks if it's null, empty or only spaces
 * @param $str The string to check
 */
function isEmpty($str)
{
    return !(isset($str) && (strlen(trim($str)) > 0));
}