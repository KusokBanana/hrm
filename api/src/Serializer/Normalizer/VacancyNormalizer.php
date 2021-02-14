<?php

namespace App\Serializer\Normalizer;

use App\Entity\Vacancy;
use App\Entity\VacancySkill;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VacancyNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($vacancy, string $format = null, array $context = []): array
    {
        /* @var $vacancy Vacancy */

        return [
            'id' => $vacancy->getId(),
            'created_at' => $this->normalizer->normalize($vacancy->getCreatedAt(), $format, $context),
            'title' => $vacancy->getTitle(),
            'description' => $vacancy->getDescription(),
            'skills' => array_map(
                fn(VacancySkill $skill) => [
                    'skill' => $this->normalizer->normalize($skill->getSkill(), $format, $context),
                    'level' => $skill->getLevel(),
                ],
                $vacancy->getSkills(),
            ),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Vacancy;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
