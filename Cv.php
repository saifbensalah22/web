<?php

namespace App\Entity;

use App\Repository\CvRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CvRepository::class)]
class Cv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ compétences ne peut pas être vide")]
    #[Assert\Type(type:"alpha" , message: "Le champ compétences doit etre une chaine de caractere")]
    private ?string $competances = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ expériences ne peut pas être vide")]
    #[Assert\Type(type:"alpha" , message: "Le champ expériences doit etre une chaine de caractere")]
    private ?string $experience = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ éducation ne peut pas être vide")]
    #[Assert\Type(type:"alpha" , message: "Le champ éducation doit etre une chaine de caractere")]
    private ?string $education = null;

    #[ORM\OneToOne(inversedBy: 'cv', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $id_user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetances(): ?string
    {
        return $this->competances;
    }

    public function setCompetances(string $competances): self
    {
        $this->competances = $competances;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getEducation(): ?string
    {
        return $this->education;
    }

    public function setEducation(string $education): self
    {
        $this->education = $education;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }
    
    public function setIdUser(User $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function __toString()
    {
        return $this->getId();
    }
}
