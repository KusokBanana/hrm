<?php

namespace App\Entity;

class EducationHistory
{
    private string $name;
    private string $organization;
    private ?int $year;

    public function __construct(string $name, string $organization, ?int $year)
    {
        $this->name = $name;
        $this->organization = $organization;
        $this->year = $year;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function getYear(): int
    {
        return $this->year;
    }
}
