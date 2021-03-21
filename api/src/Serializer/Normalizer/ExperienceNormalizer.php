<?php

namespace App\Serializer\Normalizer;

use App\Entity\Experience;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExperienceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($experience, string $format = null, array $context = []): array
    {
        /* @var $experience Experience */
        return [
            'company' => $this->normalizer->normalize($experience->getCompany(), $format, $context),
            'position' => $experience->getPosition(),
            'description' => $experience->getDescription(),
            'start' => $this->normalizer->normalize($experience->getStartedAt(), $format, $context),
            'end' => $this->normalizer->normalize($experience->getEndedAt(), $format, $context),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Experience;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
