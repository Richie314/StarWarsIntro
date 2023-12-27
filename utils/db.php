<?php
include_once "./string.php";

$parts = parse_ini_file(".env"); # Credentials are stored in a .env file
if (!isset($parts) || 
    isEmpty($parts["MYSQL_USER"]) || 
    isEmpty($parts["MYSQL_PASSWORD"]) || 
    isEmpty($parts["MYSQL_DATABASE_NAME"]) || 
    isEmpty($parts["MYSQL_HOST"]))
{
    unset($parts); # Prevent credential leaks
    die(500);
}
$db = new mysqli(
    $parts["MYSQL_HOST"], 
    $parts["MYSQL_USER"],
    isEmpty($parts["MYSQL_PASSWORD"]) ? null : $parts["MYSQL_PASSWORD"],
    $parts["MYSQL_DATABASE_NAME"]);

    unset($parts); # Prevent credential leaks
if (!$db || $db->connect_errno)
{
    die(500);
}
$db->set_charset("utf-8");