<?php

namespace App\Serializer\Normalizer;

use App\Entity\Relevance;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RelevanceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($vacancy, string $format = null, array $context = []): array
    {
        /* @var $vacancy Relevance */

        return [
            'fit' => $vacancy->getFit(),
            'vacancy' => $this->normalizer->normalize($vacancy->getVacancy(), $format, $context),
            'candidate' => $this->normalizer->normalize($vacancy->getCandidate(), $format, $context),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Relevance;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
