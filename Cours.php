<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, type:'string')]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your cours name must be at least {{ limit }} characters long',
        maxMessage: 'Your cours name cannot be longer than {{ limit }} characters',
    )]
    private $cours_name;

    #[ORM\Column(length: 255, type:'string')]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your tuteur name must be at least {{ limit }} characters long',
        maxMessage: 'Your tuteur name cannot be longer than {{ limit }} characters',
    )]
    private $nom_tuteur;

    #[ORM\Column(length: 255, type:'string')]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Your description must be at least {{ limit }} characters long',
        maxMessage: 'Your description cannot be longer than {{ limit }} characters',
    )]
    private $description;

    #[ORM\Column( type: 'float')]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]
    #[Assert\Positive(message: 'le prix ne peut être négatif')]
    private $prix;

   // #[ORM\Column(length: 255, type:'string')]
    // #[Assert\NotBlank(message:'le chmps ne doit être vide')]
   // private $upload;

    #[ORM\Column(nullable: true)]
    private ?int $occurence = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    private ?User $id_user = null;

    #[ORM\OneToOne(mappedBy: 'id_cours', cascade: ['persist', 'remove'])]
    private ?TestCours $testCours = null;

    #[ORM\OneToOne(mappedBy: 'cours', cascade: ['persist', 'remove'])]
    private ?Likes $likes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCoursName(): ?string
    {
        return $this->cours_name;
    }

    public function setCoursName(?string $cours_name): self
    {
        $this->cours_name = $cours_name;

        return $this;
    }

    public function getNomTuteur(): ?string
    {
        return $this->nom_tuteur;
    }

    public function setNomTuteur(?string $nom_tuteur): self
    {
        $this->nom_tuteur = $nom_tuteur;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(?string $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getOccurence(): ?float
    {
        return $this->occurence;
    }

    public function setOccurence(?float $occurence): self
    {
        $this->occurence = $occurence;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getTestCours(): ?TestCours
    {
        return $this->testCours;
    }

    public function setTestCours(?TestCours $testCours): self
    {
        // unset the owning side of the relation if necessary
        if ($testCours === null && $this->testCours !== null) {
            $this->testCours->setIdCours(null);
        }

        // set the owning side of the relation if necessary
        if ($testCours !== null && $testCours->getIdCours() !== $this) {
            $testCours->setIdCours($this);
        }

        $this->testCours = $testCours;

        return $this;

    }
    public function __toString(){
        return $this->getId();
    }

    /*public function getUpload(): ?string
    {
        return $this->upload;
    }

    public function setUpload(string $upload): self
    {
        $this->upload = $upload;

        return $this;
    }*/
}
