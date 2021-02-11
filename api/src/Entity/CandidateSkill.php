<?php

namespace App\Entity;

use App\Repository\CandidateSkillRepository;
use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CandidateSkillRepository::class)
 */
class CandidateSkill
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Candidate::class)
     */
    private Candidate $candidate;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Skill::class)
     * @ORM\JoinColumn(name="skill_code", referencedColumnName="code")
     */
    private Skill $skill;

    /**
     * @ORM\Column(type="float")
     */
    private float $level;

    public function __construct(Candidate $candidate, Skill $skill, float $level)
    {
        $this->candidate = $candidate;
        $this->skill     = $skill;
        $this->level     = round($level, 2, PHP_ROUND_HALF_ODD);

        Assert::that($this->level)->greaterThan(0.00);
        Assert::that($this->level)->lessOrEqualThan(1.00);
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getSkill(): Skill
    {
        return $this->skill;
    }

    public function getLevel(): float
    {
        return $this->level;
    }

    public function __toString(): string
    {
        return sprintf('Candidate %s knows %s by %f', $this->candidate->getName(), $this->skill->getName(), $this->level);
    }
}
