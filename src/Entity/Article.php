<?php
namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10, 
        max: 255,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Length(
        min: 3, 
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datecreation = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    #[Assert\Type(type: "float", message: "Le prix doit être un nombre valide.")]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité en stock est obligatoire.")]
    #[Assert\PositiveOrZero(message: "La quantité ne peut pas être négative.")]
    #[Assert\Type(type: "integer", message: "La quantité doit être un nombre entier.")]
    private ?int $quantitestock = null;

    #[ORM\OneToMany(targetEntity: ListArticle::class, mappedBy: 'article')]
    private Collection $listArticles;

    /**
     * @var Collection<int, Favorie>
     */
    #[ORM\OneToMany(targetEntity: Favorie::class, mappedBy: 'article')]
    private Collection $favories;

    public function __construct()
    {
        $this->listArticles = new ArrayCollection();
        $this->favories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setDatecreation(\DateTimeInterface $datecreation): static
    {
        $this->datecreation = $datecreation;
        return $this;
    }

    public function getDatecreation(): ?\DateTimeInterface
    {
        return $this->datecreation;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setQuantitestock(int $quantitestock): static
    {
        $this->quantitestock = $quantitestock;
        return $this;
    }

    public function getQuantitestock(): ?int
    {
        return $this->quantitestock;
    }

    public function getListArticles(): Collection
    {
        return $this->listArticles;
    }

    public function addListArticle(ListArticle $listArticle): static
    {
        if (!$this->listArticles->contains($listArticle)) {
            $this->listArticles->add($listArticle);
            $listArticle->setArticle($this);
        }
        return $this;
    }

    public function removeListArticle(ListArticle $listArticle): static
    {
        if ($this->listArticles->removeElement($listArticle)) {
            // set the owning side to null (unless already changed)
            if ($listArticle->getArticle() === $this) {
                $listArticle->setArticle(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Favorie>
     */
    public function getFavories(): Collection
    {
        return $this->favories;
    }

    public function addFavory(Favorie $favory): static
    {
        if (!$this->favories->contains($favory)) {
            $this->favories->add($favory);
            $favory->setArticle($this);
        }

        return $this;
    }

    public function removeFavory(Favorie $favory): static
    {
        if ($this->favories->removeElement($favory)) {
            // set the owning side to null (unless already changed)
            if ($favory->getArticle() === $this) {
                $favory->setArticle(null);
            }
        }

        return $this;
    }

}