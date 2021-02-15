<?php

namespace App\Mutation;

use App\Entity\Candidate;
use App\Entity\CandidateSkill;
use App\Entity\EducationHistory;
use App\Entity\Experience;
use App\Repository\SkillRepository;
use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class CandidateFactory
{
    private SkillRepository $skillRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SkillRepository $skillRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->skillRepository = $skillRepository;
        $this->entityManager = $entityManager;
    }

    public function create(ParameterBag $data): Candidate
    {
        $educationHistory = [];
        if ($data->has('education_history')) {
            $education = $data->get('education_history');

            Assert::that($education)->isArray();
            Assert::thatAll($education)->keyExists('name')->keyExists('organization');

            foreach ($education as $item) {
                $educationHistory[] = new EducationHistory(
                    $item['name'],
                    $item['organization'],
                    $item['year'] ?? null,
                );
            }
        }

        $experience = [];
        if ($data->has('experience')) {
            $experienceData = $data->get('experience');

            Assert::that($experienceData)->isArray();
            Assert::thatAll($experienceData)
                ->keyExists('position')
                ->keyExists('description')
                ->keyExists('start');

            foreach ($experienceData as $item) {
                $experience[] = new Experience(
                    $item['position'],
                    $item['description'],
                    new \DateTime($item['start']),
                    array_key_exists('end', $item) ? new \DateTime($item['end']) : null,
                );
            }
        }

        $candidate = new Candidate(
            $data->get('name'),
            $data->get('sex'),
            $data->get('city'),
            $data->get('birth_date') ? new \DateTime($data->get('birth_date')) : null,
            $data->get('title'),
            $data->get('salary'),
            $educationHistory,
            $experience,
            $data->get('languages'),
            $data->get('about'),
            $data->get('status'),
        );

        $this->entityManager->persist($candidate);

        if ($data->has('skills')) {
            $this->updateSkills($candidate, $data->get('skills'));
        }

        return $candidate;
    }

    public function update(Candidate $candidate, ParameterBag $data): void
    {
        if ($data->has('skills')) {
            $this->updateSkills($candidate, $data->get('skills'));
        }

        if ($data->has('education_history')) {
            $educationHistory = [];
            $education = $data->get('education_history');

            Assert::that($education)->isArray();
            Assert::thatAll($education)->keyExists('name')->keyExists('organization');

            foreach ($education as $item) {
                $educationHistory[] = new EducationHistory(
                    $item['name'],
                    $item['organization'],
                    $item['year'] ?? null,
                );
            }
        } else {
            $educationHistory = $candidate->getEducationHistory();
        }

        if ($data->has('experience')) {
            $experience = [];
            $experienceData = $data->get('experience');

            Assert::that($experienceData)->isArray();
            Assert::thatAll($experienceData)
                ->keyExists('position')
                ->keyExists('description')
                ->keyExists('start');

            foreach ($experienceData as $item) {
                $experience[] = new Experience(
                    $item['position'],
                    $item['description'],
                    new \DateTime($item['start']),
                    array_key_exists('end', $item) ? new \DateTime($item['end']) : null,
                );
            }
        } else {
            $experience = $candidate->getExperience();
        }

        if ($data->has('birth_date')) {
            $birthDate = $data->get('birth_date') ? new \DateTime($data->get('birth_date')) : null;
        } else {
            $birthDate = $candidate->getBirthDate();
        }

        $candidate->update(
            $data->get('name', $candidate->getName()),
            $data->get('sex', $candidate->getSex()),
            $data->get('city', $candidate->getCity()),
            $birthDate,
            $data->get('title', $candidate->getTitle()),
            $data->get('salary', $candidate->getSalary()),
            $educationHistory,
            $experience,
            $data->get('languages', $candidate->getLanguages()),
            $data->get('about', $candidate->getAbout()),
            $data->get('status', $candidate->getStatus()),
        );
    }

    public function updateSkills(Candidate $candidate, array $data): void
    {
        Assert::thatAll($data)->keyExists('skill');
        Assert::thatAll($data)->keyExists('level');

        foreach ($candidate->getSkills() as $skill) {
            $this->entityManager->remove($skill);
        }

        $this->entityManager->flush();

        foreach ($data as $item) {
            $skill = $this->skillRepository->findOneBy(['code' => $item['skill']]);
            $candidateSkill = new CandidateSkill($candidate, $skill, $item['level']);
            $this->entityManager->persist($candidateSkill);
        }
    }
}
