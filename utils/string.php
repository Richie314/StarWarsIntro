<?php
/**
 * Checks if it's null, empty or only spaces
 * @param $str The string to check
 */
function isEmpty($str) : bool
{
    return !(isset($str) && (strlen(trim($str)) > 0));
}
/**
 * Returns client's ip4 string
 */
function getUserIP() : string
{
    if (isset($_SERVER['REMOTE_ADDR']))
        return $_SERVER['REMOTE_ADDR'];
    
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];

    return "127.0.0.1";
}