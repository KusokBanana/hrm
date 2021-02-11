<?php declare(strict_types = 1);

namespace App\Doctrine\Type;

use App\Entity\Specialization;
use Assert\Assert;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Types\ArrayType;

final class SpecializationType extends ArrayType
{
    public const NAME = 'array_specialization';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        Assert::that($platform)->isInstanceOf(PostgreSQL100Platform::class);

        return 'JSONB';
    }

    public function convertToDatabaseValue($phpValue, AbstractPlatform $platform): string
    {
        /** @var Specialization[] $phpValue */
        Assert::thatAll($phpValue)->isInstanceOf(Specialization::class);

        $value = array_map(
            fn(Specialization $item) => [
                'name' => $item->getName(),
                'area' => $item->getArea(),
            ],
            $phpValue
        );

        return json_encode($value, JSON_THROW_ON_ERROR, 512);
    }

    public function convertToPHPValue($databaseValue, AbstractPlatform $platform): array
    {
        $value = json_decode($databaseValue, true, 512, JSON_THROW_ON_ERROR);

        return array_map(
            fn(array $item) => new Specialization(
                $item['name'],
                $item['area'],
            ),
            $value
        );
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
