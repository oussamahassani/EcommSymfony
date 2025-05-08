<?php

namespace App\Entity;

use App\Repository\AccordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccordRepository::class)]
class Accord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $statutDemande = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateReception = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\Column(length: 255)]
    private ?string $output = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?MaterielRecyclable $materielRecyclable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatutDemande(): ?string
    {
        return $this->statutDemande;
    }

    public function setStatutDemande(string $statutDemande): static
    {
        $this->statutDemande = $statutDemande;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateReception(): ?\DateTimeImmutable
    {
        return $this->dateReception;
    }

    public function setDateReception(?\DateTimeImmutable $dateReception): static
    {
        $this->dateReception = $dateReception;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(string $output): static
    {
        $this->output = $output;

        return $this;
    }

    public function getMaterielRecyclable(): ?MaterielRecyclable
    {
        return $this->materielRecyclable;
    }

    public function setMaterielRecyclable(?MaterielRecyclable $materielRecyclable): static
    {
        $this->materielRecyclable = $materielRecyclable;

        return $this;
    }
}
