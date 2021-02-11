<?php declare(strict_types = 1);

namespace App\Doctrine\Type;

use App\Entity\Experience;
use Assert\Assert;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Types\ArrayType;

final class ExperienceType extends ArrayType
{
    public const NAME = 'array_experience';
    private const DATE_FORMAT = 'Y-m-d';

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
        /** @var Experience[] $phpValue */
        Assert::thatAll($phpValue)->isInstanceOf(Experience::class);

        $value = array_map(
            fn(Experience $item) => [
                'position' => $item->getPosition(),
                'description' => $item->getDescription(),
                'start' => $item->getStart()->format(self::DATE_FORMAT),
                'end' => $item->hasEnd() ? $item->getEnd()->format(self::DATE_FORMAT) : null,
            ],
            $phpValue
        );

        return json_encode($value, JSON_THROW_ON_ERROR, 512);
    }

    public function convertToPHPValue($databaseValue, AbstractPlatform $platform): array
    {
        $value = json_decode($databaseValue, true, 512, JSON_THROW_ON_ERROR);

        return array_map(
            fn(array $item) => new Experience(
                $item['position'],
                $item['description'],
                \DateTime::createFromFormat(self::DATE_FORMAT, $item['start']),
                $item['end'] ? \DateTime::createFromFormat(self::DATE_FORMAT, $item['end']) : null,
            ),
            $value
        );
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
