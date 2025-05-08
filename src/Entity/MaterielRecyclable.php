<?php

namespace App\Entity;

use App\Repository\MaterielRecyclableRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\StatutEnum;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterielRecyclableRepository::class)]
class MaterielRecyclable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $datecreation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"Type Material is required")]
    #[Assert\Length(min: 2, max: 255, minMessage: 'The type of material must be at least {{ limit }} characters long.', maxMessage: 'The type of material cannot be longer than {{ limit }} characters.')]
    private ?string $type_materiel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: "string", enumType: StatutEnum::class)]
    private StatutEnum $statut;

    #[ORM\ManyToOne(inversedBy: 'materielRecyclables')]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'materielRecyclables')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDatecreation(): ?\DateTimeImmutable
    {
        return $this->datecreation;
    }

    public function setDatecreation(\DateTimeImmutable $datecreation): static
    {
        $this->datecreation = $datecreation;

        return $this;
    }

    public function getTypeMateriel(): ?string
    {
        return $this->type_materiel;
    }

    public function setTypeMateriel(string $type_materiel): static
    {
        $this->type_materiel = $type_materiel;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getStatut(): StatutEnum
    {
        return $this->statut;
    }

    public function setStatut(StatutEnum $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
