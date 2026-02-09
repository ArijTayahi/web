<?php

namespace App\Entity;

use App\Repository\StatistiquesSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatistiquesSessionRepository::class)]
class StatistiquesSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column(length: 50)]
    private ?string $qualiteConnexion = null;

    #[ORM\Column]
    private ?int $nbMessages = null;

    #[ORM\OneToOne(inversedBy: 'statistiques', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $consultation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getQualiteConnexion(): ?string
    {
        return $this->qualiteConnexion;
    }

    public function setQualiteConnexion(string $qualiteConnexion): static
    {
        $this->qualiteConnexion = $qualiteConnexion;

        return $this;
    }

    public function getNbMessages(): ?int
    {
        return $this->nbMessages;
    }

    public function setNbMessages(int $nbMessages): static
    {
        $this->nbMessages = $nbMessages;

        return $this;
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(Consultation $consultation): static
    {
        $this->consultation = $consultation;

        return $this;
    }
}
