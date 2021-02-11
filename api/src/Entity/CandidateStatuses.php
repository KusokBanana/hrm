<?php

namespace App\Entity;

use Assert\Assert;

class CandidateStatuses
{
    public const STATUS_SEARCH = 'search';
    public const STATUS_ACTIVE_SEARCH = 'active_search';
    public const STATUS_NOT_INTERESTED = 'not_interested';

    public const STATUSES = [
        self::STATUS_SEARCH,
        self::STATUS_ACTIVE_SEARCH,
        self::STATUS_NOT_INTERESTED
    ];

    public static function validate(string $status): void
    {
        Assert::that($status)->inArray(self::STATUSES);
    }
}
