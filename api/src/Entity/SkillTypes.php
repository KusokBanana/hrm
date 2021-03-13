<?php

namespace App\Entity;

use App\Helper\Slugifier;
use App\Repository\SkillRepository;
use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

class SkillTypes
{
    public const TYPE_SOFT = 'soft';
    public const TYPE_HARD = 'hard';

    public const TYPES = [
        self::TYPE_SOFT,
        self::TYPE_HARD,
    ];

    public static function validate(string $type): void
    {
        Assert::that($type)->inArray(
            self::TYPES,
            sprintf('Expected type to be one of "%s", got "%s"', join(', ', self::TYPES), $type)
        );
    }
}
