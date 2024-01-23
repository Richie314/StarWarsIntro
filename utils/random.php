<?php
function random_float ($min = 0, $max = 1) //: float
{
    return ((float)$min + lcg_value() * ((float)$max - (float)$min));
}
function random_percentage() //: string
{
    $number = random_float(0, 100);
    return sprintf("%.1f", $number) . "%";
}
function rand_int($min, $max) //: int
{
    $min = (int)$min;
    $max = (int)$max;
    
    if (function_exists('random_int'))
    {
        return random_int($min, $max);
    }
    return rand($min, $max);
}
function random_password($length = 10) //: string
{
    if (!is_int($length) || $length < 0)
    {
        throw new InvalidArgumentException("min or max were not ints!");
    }
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