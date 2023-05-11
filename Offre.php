<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idOffre = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false,referencedColumnName:"id_projet")]
    private ?Projet $id_projet = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_user = null;

    public function getIdOffre(): ?int
    {
        return $this->idOffre;
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_User): self
    {
        $this->id_user = $id_User;

        return $this;
    }

    public function getIdProjet(): ?Projet
    {
        return $this->id_projet;
    }

    public function setIdProjet(?Projet $id_projet): self
    {
        $this->id_projet = $id_projet;

        return $this;
    }
    public function __toString() {
        return $this->getIdOffre();
    }
}
