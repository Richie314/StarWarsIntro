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
    $headers = getallheaders();
    if (array_key_exists(key: 'Cf-Connecting-Ip', array: $headers)) {
        // Cloudflare tunnel forwarding
        return $headers['Cf-Connecting-Ip'];
    }
    
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] !== '::1') {
        //ip from share internet
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}
