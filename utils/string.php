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
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== "::1")
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== "::1")
        return $_SERVER['REMOTE_ADDR'];
    
    return "127.0.0.1";
}
