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

    public function normalize($object, string $format = null, array $context = []): array
    {
        /* @var $object Vacancy */

        return [
            'id' => $object->getId(),
            'created_at' => $this->normalizer->normalize($object->getCreatedAt(), $format, $context),
            'title' => $object->getTitle(),
            'company' => $this->normalizer->normalize($object->getCompany(), $format, $context),
            'description' => $object->getDescription(),
            'skills' => array_map(
                fn(VacancySkill $skill) => [
                    'skill' => $this->normalizer->normalize($skill->getSkill(), $format, $context),
                    'level' => $skill->getLevel(),
                ],
                $object->getSkills(),
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
