<?php

namespace App\Entity;

use App\Helper\Slugifier;
use App\Repository\SkillRepository;
use Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SkillRepository::class)
 */
class Skill
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $type;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class)
     * @ORM\JoinColumn(name="parent_code", referencedColumnName="code", nullable=true)
     */
    private ?Skill $parent;

    public function __construct(string $name, string $type, Skill $parent = null)
    {
        SkillTypes::validate($type);

        $this->code = Slugifier::transform($name);
        $this->name = $name;
        $this->type = $type;
        $this->parent = $parent;

        Assert::that($this->code)->notEq($parent->getCode());
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function hasParent(): bool
    {
        return $this->parent instanceof Skill;
    }

    public function getParent(): ?Skill
    {
        return $this->parent;
    }

    public function __toString(): string
    {
        return sprintf('Skill %s (%d)', $this->name, $this->code);
    }
}
