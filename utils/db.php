<?php
include_once "./utils/string.php";

$parts = parse_ini_file(".env"); # Credentials are stored in a .ini file

$DISABLE_PASSWORD_RECOVERY = !isEmpty($parts["DISABLE_PASSWORD_RECOVERY"]) && (bool)$parts["DISABLE_PASSWORD_RECOVERY"];

if (!$parts ||
    isEmpty($parts["MYSQL_USER"]) ||  
    isEmpty($parts["MYSQL_DATABASE_NAME"]) || 
    isEmpty($parts["MYSQL_HOST"]))
{
    unset($parts); # Prevent credential leaks
    throw new RuntimeException('Could not load db credentials', 500);
}
$db = new mysqli(
    $parts["MYSQL_HOST"], 
    $parts["MYSQL_USER"],
    isEmpty($parts["MYSQL_PASSWORD"]) ? null : $parts["MYSQL_PASSWORD"],
    $parts["MYSQL_DATABASE_NAME"]);

unset($parts); # Prevent credential leaks
if (!$db || $db->connect_errno)
{
    throw new RuntimeException('Could not connect to db', 500);
}
$db->set_charset("utf8"); # Prevent sql injections by passing a non utf-8 string