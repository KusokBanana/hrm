<?php

namespace App\Serializer\Normalizer;

use App\Entity\Company;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CompanyNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($company, string $format = null, array $context = []): array
    {
        /* @var $company Company */
        return [
            'id' => $company->getId(),
            'name' => $company->getName(),
            'description' => $company->getDescription(),
            'author' => [
                'username' => $company->getAuthor()->getUsername(),
            ],
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Company;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
