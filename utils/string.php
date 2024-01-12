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
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== "::1")
        return $_SERVER['REMOTE_ADDR'];
    
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] !== "::1")
        return $_SERVER['HTTP_X_FORWARDED_FOR'];

    return "127.0.0.1";
}

function getCurrentProtocol()
{
    if (
        isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            return "https://";
    }
    return "http://";
}