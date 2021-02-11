<?php declare(strict_types = 1);

namespace App\Helper;

final class Slugifier
{
    public static function transform(string $value): string
    {
        $value = mb_strtolower($value);
        $value = Transliterator::transform($value);
        $value = preg_replace('/[^a-z0-9]/', '-', $value);
        $value = preg_replace('/[-\s]+/', '-', $value);
        $value = trim($value, '-');
        return $value;
    }
}
