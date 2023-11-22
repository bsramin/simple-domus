<?php

namespace App\Util;

class StringUtil
{
    public static function extractStudentName(string $string): string|null
    {
        $pattern = '/\b[A-Z][a-zA-Z]*\s[A-Z][a-zA-Z]*\b/';
        preg_match($pattern, $string, $matches);
        return $matches[0] ?? null;
    }
}
