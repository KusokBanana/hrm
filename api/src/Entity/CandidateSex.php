<?php

namespace App\Entity;

use Assert\Assert;

class CandidateSex
{
    public const MALE = 'M';
    public const FEMALE = 'F';

    public static function validate(string $sex): void
    {
        Assert::that($sex)->inArray([self::FEMALE, self::MALE]);
    }
}
