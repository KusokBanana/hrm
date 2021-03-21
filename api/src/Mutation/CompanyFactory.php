<?php

namespace App\Mutation;

use App\Entity\Company;
use App\Entity\User;
use App\Repository\ExperienceRepository;
use App\Repository\SkillRepository;
use App\Repository\VacancyRepository;
use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class CompanyFactory
{
    private SkillRepository $skillRepository;
    private ExperienceRepository $experienceRepository;
    private VacancyRepository $vacancyRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        VacancyRepository $vacancyRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->skillRepository = $skillRepository;
        $this->experienceRepository = $experienceRepository;
        $this->vacancyRepository = $vacancyRepository;
        $this->entityManager = $entityManager;
    }

    public function create(User $user, ParameterBag $data): Company
    {
        $company = new Company(
            $user,
            $data->get('name'),
            $data->get('description'),
        );

        $this->entityManager->persist($company);

        return $company;
    }

    public function update(User $user, Company $company, ParameterBag $data): void
    {
        Assert::that($user->getUsername())->eq($company->getAuthor()->getUsername());

        $company->update(
            $data->get('name', $company->getName()),
            $data->get('description', $company->getDescription()),
        );
    }

    public function delete(Company $company): void
    {
        $experience = $this->experienceRepository->findBy(['company' => $company]);

        Assert::that($experience)->count(
            0,
            sprintf("Can't delete company '%s' because it used in candidates' experience.", $company->getId())
        );

        $vacancies = $this->vacancyRepository->findBy(['company' => $company]);
        Assert::that($vacancies)->count(
            0,
            sprintf("Can't delete company '%s' because it used by vacancies.", $company->getId())
        );

        $this->entityManager->remove($company);
    }
}
