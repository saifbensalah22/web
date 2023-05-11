<?php

namespace App\Entity;

use App\Repository\DemandeOffreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeOffreRepository::class)]
class DemandeOffre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reponseDemande = null;

    #[ORM\Column]
    private ?int $idFreelance = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Demande $idDemande = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getReponseDemande(): ?string
    {
        return $this->reponseDemande;
    }
    public function __toString()
    {
        return $this->getId();
    }

    public function setReponseDemande(string $reponseDemande): self
    {
        $this->reponseDemande = $reponseDemande;

        return $this;
    }

    public function getIdFreelance(): ?int
    {
        return $this->idFreelance;
    }

    public function setIdFreelance(int $idFreelance): self
    {
        $this->idFreelance = $idFreelance;

        return $this;
    }

    public function getIdDemande(): ?Demande
    {
        return $this->idDemande;
    }

    public function setIdDemande(?Demande $idDemande): self
    {
        $this->idDemande = $idDemande;

        return $this;
    }
    
}
