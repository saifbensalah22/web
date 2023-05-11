<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contenu_test = null;

    #[ORM\Column(length: 255)]
    private ?string $test_name = null;

    #[ORM\Column]
    private ?float $note = null;

    #[ORM\OneToOne(inversedBy: 'test', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?cours $id_cours = null;

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

    public function setTestName(string $test_name): self
    {
        $this->test_name = $test_name;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(float $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getIdCours(): ?cours
    {
        return $this->id_cours;
    }

    public function setIdCours(cours $id_cours): self
    {
        $this->id_cours = $id_cours;

        return $this;
    }
}
