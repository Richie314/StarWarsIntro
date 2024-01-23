<?php
function random_float ($min = 0, $max = 1) //: float
{
    if (!is_float($min) || !is_float($max))
    {
        throw new InvalidArgumentException("min or max were not floats!");
    }
    return ($min + lcg_value() * ($max - $min));
}
function random_percentage() //: string
{
    $number = random_float(0, 100);
    return sprintf("%.1f", $number) . "%";
}
function rand_int($min, $max) //: int
{
    if (!is_int($min) || !is_int($max))
    {
        throw new InvalidArgumentException("min or max were not ints!");
    }
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