<?php

namespace App\Helpers;

class PriceHelper
{
    public static function minorToMajor(int $price): string
    {
        return number_format($price / 100, 2, '.', '');
    }

}