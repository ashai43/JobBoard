<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 'today')]
    #[Assert\NotNull()]
    #[Assert\Type(\DateTime::class)]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank()]
    #[Assert\Choice(
        choices: ['pending', 'accepted', 'refused'],
        message: 'Choose a valid status.'
    )]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NoSuspiciousCharacters]
    private ?string $lettreMotivation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NoSuspiciousCharacters]
    #[Assert\File(
        maxSize: '1024k',
        extensions: ['pdf'],
        extensionsMessage: 'Please upload a valid PDF',
    )]
    private ?string $cv = null;

    #[Assert\File(
        maxSize: '1024k',
        extensions: ['pdf'],
        extensionsMessage: 'Please upload a valid PDF',
    )]
    private ?UploadedFile $cvFile = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    private ?Candidat $idCandidat = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    private ?Annonce $idAnnonce = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getLettreMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(?string $lettreMotivation): static
    {
        $this->lettreMotivation = $lettreMotivation;

        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    public function getCvFile(): ?UploadedFile
    {
        return $this->cvFile;
    }

    public function setCvFile(?UploadedFile $cvFile): static
    {
        $this->cvFile = $cvFile;

        return $this;
    }

    public function getIdCandidat(): ?Candidat
    {
        return $this->idCandidat;
    }

    public function setIdCandidat(?Candidat $idCandidat): static
    {
        $this->idCandidat = $idCandidat;

        return $this;
    }

    public function getIdAnnonce(): ?Annonce
    {
        return $this->idAnnonce;
    }

    public function setIdAnnonce(?Annonce $idAnnonce): static
    {
        $this->idAnnonce = $idAnnonce;

        return $this;
    }
}
