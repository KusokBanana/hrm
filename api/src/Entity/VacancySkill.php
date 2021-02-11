<?php

namespace App\Entity;

use App\Repository\VacancySkillRepository;
use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VacancySkillRepository::class)
 */
class VacancySkill
{
    /**
     * @ORM\ManyToOne(targetEntity=Vacancy::class)
     */
    private Vacancy $vacancy;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class)
     */
    private Skill $skill;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $level;

    public function __construct(Vacancy $vacancy, Skill $skill, ?float $level)
    {
        if ($level !== null) {
            $level = round($level, 2, PHP_ROUND_HALF_ODD);
        }

        $this->vacancy = $vacancy;
        $this->skill   = $skill;
        $this->level   = $level;

        Assert::that($this->level)->nullOr()->greaterThan(0.00);
        Assert::that($this->level)->nullOr()->lessOrEqualThan(1.00);
    }

    public function getVacancy(): Vacancy
    {
        return $this->vacancy;
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function getLevel(): ?float
    {
        return $this->level;
    }

    public function __toString(): string
    {
        return sprintf('Vacancy "%s" wants candidate who knows "%s"', $this->vacancy->getTitle(), $this->skill->getName());
    }
}
