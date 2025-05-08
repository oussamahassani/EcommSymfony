<?php
namespace App\Entity;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[ORM\Table(name: 'entreprise')]
class Entreprise implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Company name is required")]
    private ?string $company_name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Email is required")]
    #[Assert\Email(message: "The email {{ value }} is not a valid email address")]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Phone number is required")]
    private ?string $phone = null;

    #[ORM\Embedded(class: Address::class)]
    private Address $address;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Tax Code is required")]
    #[Assert\Length(min: 1, max: 20, minMessage: "Tax Code must contain at least {{ limit }} characters.", maxMessage: "Tax Code must be less than {{ limit }} characters.")]
    private ?string $tax_code = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'entreprises')]
    private ?Role $role = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $supplier = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Paswword is required")]
    #[Assert\Length(min: 8, max: 255, minMessage: "Your password must contain at least {{ limit }} characters.", maxMessage: "Your password must be less than {{ limit }} characters.")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Field is required")]
    private ?string $field = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    /**
     * @var Collection<int, MaterielRecyclable>
     */
    #[ORM\OneToMany(targetEntity: MaterielRecyclable::class, mappedBy: 'entreprise')]
    private Collection $materielRecyclables;


    public function __construct()
    {
        $this->address = new Address();
        $this->materielRecyclables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(string $company_name): static
    {
        $this->company_name = $company_name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getTaxCode(): ?string
    {
        return $this->tax_code;
    }

    public function setTaxCode(string $tax_code): static
    {
        $this->tax_code = $tax_code;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function eraseCredentials()
    {
        // No credentials to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function isSupplier(): ?bool
    {
        return $this->supplier;
    }

    public function setSupplier(bool $supplier): static
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->getRole() ? [$this->getRole()->getName()] : [];
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    /**
     * @return Collection<int, MaterielRecyclable>
     */
    public function getMaterielRecyclables(): Collection
    {
        return $this->materielRecyclables;
    }

    public function addMaterielRecyclable(MaterielRecyclable $materielRecyclable): static
    {
        if (!$this->materielRecyclables->contains($materielRecyclable)) {
            $this->materielRecyclables->add($materielRecyclable);
            $materielRecyclable->setEntreprise($this);
        }

        return $this;
    }

    public function removeMaterielRecyclable(MaterielRecyclable $materielRecyclable): static
    {
        if ($this->materielRecyclables->removeElement($materielRecyclable)) {
            // set the owning side to null (unless already changed)
            if ($materielRecyclable->getEntreprise() === $this) {
                $materielRecyclable->setEntreprise(null);
            }
        }

        return $this;
    }

}