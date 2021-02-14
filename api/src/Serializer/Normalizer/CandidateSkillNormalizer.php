<?php

namespace App\Serializer\Normalizer;

use App\Entity\CandidateSkill;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CandidateSkillNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($skill, string $format = null, array $context = []): array
    {
        /* @var $skill CandidateSkill */

        return [
            'skill' => $this->normalizer->normalize($skill->getSkill(), $format, $context),
            'candidate' => $this->normalizer->normalize($skill->getCandidate(), $format, $context),
            'level' => $skill->getLevel(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof CandidateSkill;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
