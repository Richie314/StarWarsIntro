<?php
function random_float (float $min = 0, float $max = 1):float {
    return ($min + lcg_value() * (abs($max - $min)));
}
function random_percentage():string {
    $number = random_float(0, 100);
    return sprintf("%.1f", $number) . "%";
}