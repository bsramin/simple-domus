<?php

namespace App\Util;

use DateTimeImmutable;
use Throwable;

class DateUtil
{
    public static function convertToItalianDate(string $date): string
    {
        try {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $date);
            if ($date instanceof DateTimeImmutable) {
                return $date->format('d/m/Y');
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }
}
