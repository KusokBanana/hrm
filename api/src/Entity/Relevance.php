<?php

namespace App\Entity;

use App\Repository\RelevanceRepository;
use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RelevanceRepository::class)
 */
class Relevance
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Candidate::class, inversedBy="relevance")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private Candidate $candidate;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Vacancy::class)
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private Vacancy $vacancy;

    /**
     * @ORM\Column(type="float")
     */
    private float $fit;

    public function __construct(Candidate $candidate, Vacancy $vacancy, float $fit)
    {
        Assert::that($fit)->greaterThan(0)->lessThan(1);

        $this->candidate = $candidate;
        $this->vacancy = $vacancy;
        $this->fit = $fit;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getVacancy(): Vacancy
    {
        return $this->vacancy;
    }

    public function getFit(): float
    {
        return $this->fit;
    }
}
