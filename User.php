<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;






#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('mail', message: "Cette adresse email est déjà utilisée")]
#[ORM\Table(name: '`user`')]

class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Le champ nom ne peut pas être vide")]
    #[Assert\Type(type: "alpha", message: "Le champ nom doit etre une chaine de caractere")]
    private $nom;


    #[ORM\Column(type: "string", length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Le champ nom ne peut pas être vide")]
    #[Assert\Type(type: "alpha", message: "Le champ nom doit etre une chaine de caractere")]
    private  $prenom;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ email ne peut pas être vide")]
    #[Assert\Email(message: "Veuillez saisir un email valide")]
    private $mail;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ mot de passe ne peut pas être vide")]
    #[Assert\Length(
        min: 8,
        minMessage: 'Your password must be at least 8 characters long',

    )]
    private $password;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le champ numéro de téléphone ne peut pas être vide")]
    #[Assert\Regex(
        pattern: '/^[0-9]{8}$/',
        message: "Le numéro de téléphone doit contenir 7 chiffres"
    )]
    private $numero_telephone;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reset_token = null;




    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isBlocked = false;




    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ rôle ne peut pas être vide")]
    private $role = "simple user";


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le champ adresse ne peut pas être vide")]
    private $addresse;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToOne(mappedBy: 'id_user', cascade: ['persist', 'remove'])]
    private ?Cv $cv = null;



    #[ORM\OneToMany(mappedBy: 'id_user', targetEntity: Evenement::class)]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'id_user', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'id_user', targetEntity: Projet::class)]
    private Collection $projets;


    #[ORM\OneToMany(mappedBy: 'id_user', targetEntity: Cours::class)]
    private Collection $cours;

    #[ORM\OneToMany(mappedBy: 'id_user', targetEntity: Offre::class)]
    private Collection $offres;

    #[ORM\OneToMany(mappedBy: 'idClient', targetEntity: Demande::class)]
    private Collection $demandes;
   

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->projets = new ArrayCollection();
        $this->cours = new ArrayCollection();
        $this->offres = new ArrayCollection();
        $this->demandes = new ArrayCollection();
    }


    public function getIsBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }


    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getNumeroTelephone(): ?int
    {
        return $this->numero_telephone;
    }

    public function setNumeroTelephone(int $numero_telephone): self
    {
        $this->numero_telephone = $numero_telephone;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getAddresse(): ?string
    {
        return $this->addresse;
    }

    public function setAddresse(string $addresse): self
    {
        $this->addresse = $addresse;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCv(): ?Cv
    {
        return $this->cv;
    }

    public function setCv(Cv $cv): self
    {
        // set the owning side of the relation if necessary
        if ($cv->getIdUser() !== $this) {
            $cv->setIdUser($this);
        }

        $this->cv = $cv;

        return $this;
    }
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setIdUser($this);
        }

        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        if ($this->evenements->removeElement($evenement)) {
            // set the owning side to null (unless already changed)
            if ($evenement->getIdUser() === $this) {
                $evenement->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setIdUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getIdUser() === $this) {
                $reservation->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Projet>
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets->add($projet);
            $projet->setIdUser($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getIdUser() === $this) {
                $projet->setIdUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setIdUser($this);
        }

        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->removeElement($cour)) {
            // set the owning side to null (unless already changed)
            if ($cour->getIdUser() === $this) {
                $cour->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): self
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setIdUser($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): self
    {
        if ($this->offres->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getIdUser() === $this) {
                $offre->setIdUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials()
    {
    }
    public function getSalt()
    {
    }

    public function getRoles()
    {
        return array($this->getRole());
    }

    public function getUsername(): string
    {
        return (string) $this->mail;
    }

    public function getUserIdentifier()
    {
        return (string) $this->mail;
    }

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setIdClient($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getIdClient() === $this) {
                $demande->setIdClient(null);
            }
        }

        return $this;
    }

    
}
