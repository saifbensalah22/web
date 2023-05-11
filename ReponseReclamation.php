<?php

namespace App\Entity;

use App\Repository\ReponseReclamationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseReclamationRepository::class)]
class ReponseReclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'reponseReclamation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $id_reclamation = null;

    #[ORM\Column(length: 255)]
    private ?string $traitement = null;

    #[ORM\Column(length: 255)]
    private ?string $contenu_reponse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdReclamation(): ?reclamation
    {
        return $this->id_reclamation;
    }

    public function setIdReclamation(reclamation $id_reclamation): self
    {
        $this->id_reclamation = $id_reclamation;

        return $this;
    }

    public function getTraitement(): ?string 
    {
        return $this->traitement;
    }

    public function setTraitement(string $traitement): self
    {
        $this->traitement = $traitement;

        return $this;
    }

    public function getContenuReponse(): ?string
    {
        return $this->contenu_reponse;
    }

    public function setContenuReponse(string $contenu_reponse): self
    {
        $this->contenu_reponse = $contenu_reponse;

        return $this;
    }
}
