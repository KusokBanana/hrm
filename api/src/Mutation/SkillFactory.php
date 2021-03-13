<?php

namespace App\Mutation;

use App\Entity\Skill;
use App\Repository\CandidateSkillRepository;
use App\Repository\SkillRepository;
use App\Repository\VacancySkillRepository;
use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class SkillFactory
{
    private EntityManagerInterface $entityManager;
    private SkillRepository $skillRepository;
    private CandidateSkillRepository $candidateSkillRepository;
    private VacancySkillRepository $vacancySkillRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SkillRepository $skillRepository,
        CandidateSkillRepository $candidateSkillRepository,
        VacancySkillRepository $vacancySkillRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->skillRepository = $skillRepository;
        $this->candidateSkillRepository = $candidateSkillRepository;
        $this->vacancySkillRepository = $vacancySkillRepository;
    }

    public function create(ParameterBag $data): Skill
    {
        $parent = null;
        if ($data->has('parent_code')) {
            $parentCode = $data->get('parent_code');
            $parent = $this->skillRepository->find($parentCode);
            Assert::that($parent)->isInstanceOf(Skill::class, sprintf("Can't find skill with code '%s'", $parentCode));
        }

        $skill = new Skill(
            $data->get('name'),
            $data->get('type'),
            $parent,
        );

        $duplicate = $this->skillRepository->find($skill->getCode());
        Assert::that($duplicate)->null(function () use ($duplicate) {
            return sprintf('Skill with with similar name and same code already exists. Maybe you should use "%s"?', $duplicate->getName());
        });

        $this->entityManager->persist($skill);

        return $skill;
    }

    public function update(Skill $skill, ParameterBag $data): void
    {
        $parent = null;
        if ($data->has('parent_code')) {
            $parentCode = $data->get('parent_code');
            $parent = $this->skillRepository->find($parentCode);
            Assert::that($parent)->isInstanceOf(Skill::class, sprintf("Can't find skill with code '%s'", $parentCode));
        }

        $skill->update(
            $data->get('name'),
            $data->get('type'),
            $parent,
        );
    }

    public function delete(Skill $skill): void
    {
        $children = $this->skillRepository->findBy(['parent' => $skill]);
        $childrenNames = array_map(
            fn(Skill $skill) => $skill->getName(),
            $children
        );

        Assert::that($children)->count(
            0,
            sprintf(
                "Can't delete skill '%s' because it has children (%s). You should delete them at first.",
                $skill->getCode(), join(', ', $childrenNames)
            )
        );

        $candidateSkills = $this->candidateSkillRepository->findBy(['skill' => $skill]);
        Assert::that($candidateSkills)->count(
            0,
            sprintf("Can't delete skill '%s' because it used by candidates.", $skill->getCode())
        );

        $vacancySkills = $this->vacancySkillRepository->findBy(['skill' => $skill]);
        Assert::that($vacancySkills)->count(
            0,
            sprintf("Can't delete skill '%s' because it used by vacancies.", $skill->getCode())
        );

        $this->entityManager->remove($skill);
    }
}
