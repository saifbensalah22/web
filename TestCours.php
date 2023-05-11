<?php

namespace App\Entity;

use App\Repository\TestCoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TestCoursRepository::class)]
class TestCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]

    private ?string $contenu_test = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type(type:"alpha")]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your test name must be at least {{ limit }} characters long',
        maxMessage: 'Your test name cannot be longer than {{ limit }} characters',
    )]
    private $test_name;

    #[ORM\Column(nullable: true,type:'float')]
    #[Assert\NotBlank(message:'le chmps ne doit être vide')]
    #[Assert\PositiveOrZero(message: 'le prix ne peut être négatif')]

    private $note;

    #[ORM\OneToOne(inversedBy: 'testCours', cascade: ['persist', 'remove'])]
    private ?Cours $id_cours = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuTest(): ?string
    {
        return $this->contenu_test;
    }

    public function setContenuTest(?string $contenu_test): self
    {
        $this->contenu_test = $contenu_test;

        return $this;
    }

    public function getTestName(): ?string
    {
        return $this->test_name;
    }

    public function setTestName(?string $test_name): self
    {
        $this->test_name = $test_name;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getIdCours(): ?Cours
    {
        return $this->id_cours;
    }

    public function setIdCours(?Cours $id_cours): self
    {
        $this->id_cours = $id_cours;

        return $this;
    }
    public function __toString(){
        return $this->getId();
    }
}
