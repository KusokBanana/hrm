<?php

namespace App\Entity;

use App\Repository\VacancyRepository;
use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VacancyRepository::class)
 */
class Company
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private string $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $name;

    /**
     * @ORM\Column(type="text")
     */
    private string $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, name="author_login", referencedColumnName="login")
     */
    private User $author;

    public function __construct(User $author, string $name, string $description)
    {
        Assert::that($name)->minLength(1)->maxLength(255);
        Assert::that($description)->minLength(1);

        $this->author = $author;
        $this->name = $name;
        $this->description = $description;
    }

    public function update(string $name, string $description): void
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }
}
