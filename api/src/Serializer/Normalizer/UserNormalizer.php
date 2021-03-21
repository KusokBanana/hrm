<?php

namespace App\Serializer\Normalizer;

use App\Entity\Candidate;
use App\Entity\User;
use App\Entity\Vacancy;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($user, string $format = null, array $context = []): array
    {
        /* @var $user User */
        return [
            'login' => $user->getUsername(),
            'candidates' => array_map(
                fn(Candidate $candidate) => $this->normalizer->normalize($candidate, $format, $context),
                $user->getCandidates(),
            ),
            'vacancies' => array_map(
                fn(Vacancy $vacancy) => $this->normalizer->normalize($vacancy, $format, $context),
                $user->getVacancies(),
            ),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
