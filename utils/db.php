<?php
include_once "./utils/string.php";

# $parts = parse_ini_file(".env"); # Credentials are stored in a .ini file
if (!$parts ||
    isEmpty(getenv("MYSQL_USER")) ||  
    isEmpty(getenv("MYSQL_DATABASE_NAME")) || 
    isEmpty(getenv("MYSQL_HOST")))
{
    unset($parts); # Prevent credential leaks
    throw new RuntimeException('Could not load db credentials', 500);
}
$db = new mysqli(
    getenv("MYSQL_HOST"), 
    getenv("MYSQL_USER"),
    isEmpty(getenv("MYSQL_PASSWORD")) ? null : getenv("MYSQL_PASSWORD"),
    getenv("MYSQL_DATABASE_NAME"));

unset($parts); # Prevent credential leaks
if (!$db || $db->connect_errno)
{
    throw new RuntimeException('Could not connect to db', 500);
}
$db->set_charset("utf8"); # Prevent sql injections by passing a non utf-8 string