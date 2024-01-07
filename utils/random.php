<?php
function random_float (float $min = 0, float $max = 1):float {
    return ($min + lcg_value() * (abs($max - $min)));
}
function random_percentage():string {
    $number = random_float(0, 100);
    return sprintf("%.1f", $number) . "%";
}
function random_password(int $length = 10)
{
    $alphabet = "abcdefghijklmnopqrstuvwxyz";
    $digits = "0123456789";
    $symbols = "!|?=/&$-^";
    $chars = str_split($alphabet . strtoupper($alphabet) . $digits . $symbols);
    
    $password = "";
    for ($i = 0; $i < $length; $i++)
    {
        $password .= $chars[random_int(0, count($chars) - 1)];
    }
    return $password;
}