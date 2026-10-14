<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[UniqueEntity('nom')]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank()]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\- ]+$/',
        message: 'Le nom ne doit contenir que des lettres.'
    )]
    #[Assert\Length(
        min: 2,
        minMessage: 'Le nom doit comporter au moins 2 caractères.',
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{M}\p{N}\p{P}\p{S}\p{Zs}]+$/u',
        message: "L'adresse contient des caractères non pris en charge."
    )]
    #[Assert\Length(
        min: 2,
        minMessage: "L'adresse doit comporter au moins 2 caractères.",
    )]
    private ?string $adresse = null;

    /**
     * @var Collection<int, Annonce>
     */
    #[ORM\OneToMany(targetEntity: Annonce::class, mappedBy: 'idEntreprise')]
    private Collection $annonces;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Candidat $compteCandidat = null;

    public function __construct()
    {
        $this->annonces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Annonce>
     */
    public function getAnnonces(): Collection
    {
        return $this->annonces;
    }

    public function addAnnonce(Annonce $annonce): static
    {
        if (!$this->annonces->contains($annonce)) {
            $this->annonces->add($annonce);
            $annonce->setIdEntreprise($this);
        }

        return $this;
    }

    public function removeAnnonce(Annonce $annonce): static
    {
        if ($this->annonces->removeElement($annonce)) {
            // set the owning side to null (unless already changed)
            if ($annonce->getIdEntreprise() === $this) {
                $annonce->setIdEntreprise(null);
            }
        }

        return $this;
    }

    public function getCompteCandidat(): ?Candidat
    {
        return $this->compteCandidat;
    }

    public function setCompteCandidat(?Candidat $compteCandidat): static
    {
        $this->compteCandidat = $compteCandidat;

        return $this;
    }
}
