<?php

namespace App\Serializer\Normalizer;

use App\Entity\Skill;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SkillNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($skill, string $format = null, array $context = []): array
    {
        /* @var $skill Skill */

        return [
            'id' => $skill->getId(),
            'name' => $skill->getName(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Skill;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
