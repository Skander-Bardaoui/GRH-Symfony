<?php

namespace App\Entity;

use App\Repository\ReunionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReunionRepository::class)]
class Reunion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    private ?Employee $employee = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $desc): self { $this->description = $desc; return $this; }

    public function getEmployee(): ?Employee { return $this->employee; }
    public function setEmployee(?Employee $employee): self { $this->employee = $employee; return $this; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
}
