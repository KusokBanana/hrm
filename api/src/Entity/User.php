<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Assert\Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private string $login;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles;

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private ?string $token;

    /**
     * @ORM\OneToMany(targetEntity=Candidate::class, mappedBy="author")
     */
    private Collection $candidates;

    /**
     * @ORM\OneToMany(targetEntity=Vacancy::class, mappedBy="author")
     */
    private Collection $vacancies;

    public function __construct(string $login, array $roles = [])
    {
        Assert::that($login)->minLength(3)->maxLength(50);
        Assert::thatAll($roles)->string();

        $this->login = $login;
        $this->roles = array_unique([...$roles, 'ROLE_USER']);
        $this->token = null;
        $this->candidates = new ArrayCollection();
        $this->vacancies = new ArrayCollection();
    }

    public function getUsername(): string
    {
        return $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
//        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';

        return $this->roles;
//        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        Assert::that($password)->minLength(5)->maxLength(100);
        $this->password = $password;

        return $this;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Candidate[]
     */
    public function getCandidates(): array
    {
        return $this->candidates->toArray();
    }

    public function addCandidate(Candidate $candidate): self
    {
        $this->candidates->add($candidate);
        return $this;
    }

    /**
     * @return Vacancy[]
     */
    public function getVacancies(): array
    {
        return $this->vacancies->toArray();
    }

    public function addVacancy(Vacancy $vacancy): self
    {
        $this->vacancies->add($vacancy);
        return $this;
    }
}
