<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=Experience::class)
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="experience_unique",
 *            columns={"candidate_id", "company_id", "started_at"})
 *    }
 * )
 */
class Experience
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Candidate::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Candidate $candidate;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Company $company;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $position;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTimeInterface $startedAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $endedAt;

    public function __construct(
        Candidate $candidate,
        Company $company,
        string $position,
        ?string $description,
        \DateTimeInterface $startedAt,
        ?\DateTimeInterface $endedAt
    )
    {
        $this->candidate = $candidate;
        $this->company = $company;
        $this->position = $position;
        $this->description = $description;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function hasEndedAt(): bool
    {
        return $this->endedAt instanceof \DateTimeInterface;
    }
}
