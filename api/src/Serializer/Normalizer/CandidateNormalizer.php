<?php

namespace App\Serializer\Normalizer;

use App\Entity\Candidate;
use App\Entity\CandidateSkill;
use App\Entity\EducationHistory;
use App\Entity\Experience;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CandidateNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($candidate, string $format = null, array $context = []): array
    {
        /* @var $candidate Candidate */
        return [
            'id' => $candidate->getId(),
            'name' => $candidate->getName(),
            'sex' => $candidate->getSex(),
            'city' => $candidate->getCity(),
            'birth_date' => $this->normalizer->normalize($candidate->getBirthDate(), $format, $context),
            'title' => $candidate->getTitle(),
            'about' => $candidate->getAbout(),
            'status' => $candidate->getStatus(),
//            'specialization' => array_map(
//                fn(Specialization $specialization) => [
//                    'name' => $specialization->getName(),
//                    'area' => $specialization->getArea(),
//                ],
//                $candidate->getSpecialization()
//            ),
            'salary' => $candidate->getSalary(),
//            'education' => $candidate->getEducation(),
            'education_history' => array_map(
                fn(EducationHistory $educationHistory) => [
                    'name' => $educationHistory->getName(),
                    'organization' => $educationHistory->getOrganization(),
                    'year' => $educationHistory->getYear(),
                ],
                $candidate->getEducationHistory(),
            ),
            'experience' => array_map(
                fn(Experience $experience) => [
                    'position' => $experience->getPosition(),
                    'description' => $experience->getDescription(),
                    'start' => $this->normalizer->normalize($experience->getStart(), $format, $context),
                    'end' => $this->normalizer->normalize($experience->getEnd(), $format, $context),
                ],
                $candidate->getExperience()
            ),
            'languages' => $candidate->getLanguages(),
            'skills' => array_map(
                fn(CandidateSkill $skill) => [
                    'skill' => $this->normalizer->normalize($skill->getSkill(), $format, $context),
                    'level' => $skill->getLevel(),
                ],
                $candidate->getSkills(),
            ),
//            'most_relevant' => $candidate->hasMostRelevant() ? [
//                'fit' => $candidate->getMostRelevant()->getFit(),
//                'vacancy' => $candidate->getMostRelevant()->getVacancy()->getTitle(),
//            ] : null,
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Candidate;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
