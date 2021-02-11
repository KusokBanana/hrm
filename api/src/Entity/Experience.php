<?php

namespace App\Entity;

class Experience
{
    private string $position;
    private string $description;
    private \DateTimeInterface $start;
    private ?\DateTimeInterface $end;

    public function __construct(string $position, string $description, \DateTimeInterface $start, ?\DateTimeInterface $end)
    {
        $this->position = $position;
        $this->description = $description;
        $this->start = $start;
        $this->end = $end;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function hasEnd(): bool
    {
        return $this->end instanceof \DateTimeInterface;
    }
}
