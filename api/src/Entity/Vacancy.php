<?php

namespace App\Entity;

use App\Repository\VacancyRepository;
use Assert\Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VacancyRepository::class)
 */
class Vacancy
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text")
     */
    private string $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\OneToMany(targetEntity=VacancySkill::class, mappedBy="vacancy")
     */
    private Collection $skills;

//    /**
//     * @ORM\OneToMany(targetEntity=Relevance::class, mappedBy="vacancy")
//     */
//    private Collection $relevance;

    public function __construct(string $title, string $description, array $skills)
    {
        Assert::thatAll($skills)->isInstanceOf(VacancySkill::class);
        Assert::that($title)->minLength(1);
        Assert::that($description)->minLength(1);

        $this->createdAt = new \DateTime();
        $this->title = $title;
        $this->description = $description;
        $this->skills = new ArrayCollection($skills);
//        $this->relevance = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return VacancySkill[]
     */
    public function getSkills(): array
    {
        return $this->skills->toArray();
    }

    public function addSkill(VacancySkill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills[] = $skill;
        }

        return $this;
    }

//    /**
//     * @return Relevance[]
//     */
//    public function getRelevance(): array
//    {
//        return $this->relevance->toArray();
//    }
//
//    public function addRelevance(Relevance $relevance): self
//    {
//        if (!$this->relevance->contains($relevance)) {
//            $this->relevance[] = $relevance;
//            $relevance->setVacancy($this);
//        }
//
//        return $this;
//    }
//
//    public function removeRelevance(Relevance $relevance): self
//    {
//        if ($this->relevance->removeElement($relevance)) {
//            // set the owning side to null (unless already changed)
//            if ($relevance->getVacancy() === $this) {
//                $relevance->setVacancy(null);
//            }
//        }
//
//        return $this;
//    }
}
