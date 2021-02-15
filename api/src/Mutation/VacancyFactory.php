<?php

namespace App\Mutation;

use App\Entity\Candidate;
use App\Entity\CandidateSkill;
use App\Entity\EducationHistory;
use App\Entity\Experience;
use App\Entity\Vacancy;
use App\Entity\VacancySkill;
use App\Repository\SkillRepository;
use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class VacancyFactory
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

    public function create(ParameterBag $data): Vacancy
    {
        $vacancy = new Vacancy(
            $data->get('title'),
            $data->get('description'),
        );

        $this->entityManager->persist($vacancy);

        if ($data->has('skills')) {
            $this->updateSkills($vacancy, $data->get('skills'));
        }

        return $vacancy;
    }

    public function update(Vacancy $vacancy, ParameterBag $data): void
    {
        if ($data->has('skills')) {
            $this->updateSkills($vacancy, $data->get('skills'));
        }

        $vacancy->update(
            $data->get('title', $vacancy->getTitle()),
            $data->get('description', $vacancy->getDescription()),
        );
    }

    public function updateSkills(Vacancy $vacancy, array $data): void
    {
        Assert::thatAll($data)->keyExists('skill');

        foreach ($vacancy->getSkills() as $skill) {
            $this->entityManager->remove($skill);
        }

        $this->entityManager->flush();

        foreach ($data as $item) {
            $skill = $this->skillRepository->findOneBy(['code' => $item['skill']]);
            $vacancySkill = new VacancySkill($vacancy, $skill, $item['level'] ?? null);
            $this->entityManager->persist($vacancySkill);
        }
    }
}
