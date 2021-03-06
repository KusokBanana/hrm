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
     * @ORM\ManyToOne(targetEntity=Company::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Company $company;

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

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="vacancies")
     * @ORM\JoinColumn(nullable=false, name="author_login", referencedColumnName="login")
     */
    private User $author;

//    /**
//     * @ORM\OneToMany(targetEntity=Relevance::class, mappedBy="vacancy")
//     */
//    private Collection $relevance;

    public function __construct(Company $company, User $author, string $title, string $description)
    {
        Assert::that($title)->minLength(1);
        Assert::that($description)->minLength(1);

        $this->skills = new ArrayCollection();
//        $this->relevance = new ArrayCollection();
        $this->createdAt = new \DateTime();

        $this->company = $company;
        $this->author = $author;
        $this->title = $title;
        $this->description = $description;
    }

    public function update(string $title, string $description): void
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @return VacancySkill[]
     */
    public function getSkills(): array
    {
        return $this->skills->toArray();
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
