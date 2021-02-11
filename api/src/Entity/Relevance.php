<?php

namespace App\Entity;

use App\Repository\RelevanceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RelevanceRepository::class)
 */
class Relevance
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Candidate::class, inversedBy="relevance")
     * @ORM\JoinColumn(nullable=false)
     */
    private Candidate $candidate;

    /**
     * @ORM\ManyToOne(targetEntity=Vacancy::class, inversedBy="relevance")
     */
    private Vacancy $vacancy;

    /**
     * @ORM\Column(type="float")
     */
    private float $fit;

    public function __construct(Candidate $candidate, Vacancy $vacancy, float $fit)
    {
        $this->candidate = $candidate;
        $this->vacancy = $vacancy;
        $this->fit = $fit;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(Candidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getVacancy(): Vacancy
    {
        return $this->vacancy;
    }

    public function setVacancy(Vacancy $vacancy): self
    {
        $this->vacancy = $vacancy;

        return $this;
    }

    public function getFit(): float
    {
        return $this->fit;
    }
}
