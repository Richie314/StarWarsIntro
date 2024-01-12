<?php
function random_float (float $min = 0, float $max = 1) : float
{
    return ($min + lcg_value() * ($max - $min));
}
function random_percentage() : string
{
    $number = random_float(0, 100);
    return sprintf("%.1f", $number) . "%";
}
function rand_int(int $min, int $max) : int
{
    if (function_exists('random_int'))
    {
        return random_int($min, $max);
    }
    return rand($min, $max);
}
function random_password(int $length = 10) : string
{
    $alphabet = "abcdefghijklmnopqrstuvwxyz";
    $digits = "0123456789";
    $symbols = "!|?=/&$-^";
    $chars = str_split($alphabet . strtoupper($alphabet) . $digits . $symbols);
    
    $password = "";
    for ($i = 0; $i < abs($length); $i++)
    {
        $password .= $chars[rand_int(0, count($chars) - 1)];
    }
    return $password;
}