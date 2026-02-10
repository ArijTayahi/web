<?php

namespace App\Entity;

use App\Repository\SalleAttenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleAttenteRepository::class)]
class SalleAttente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $arriveAt;

    #[ORM\Column(length: 50, nullable: false)]
    private string $status;

    #[ORM\OneToOne(inversedBy: 'salleAttente', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $consultation = null;

    public function __construct()
    {
        $this->arriveAt = new \DateTimeImmutable();
        $this->status = 'EN_ATTENTE';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArriveAt(): ?\DateTimeImmutable
    {
        return $this->arriveAt;
    }

    public function setArriveAt(\DateTimeImmutable $arriveAt): static
    {
        $this->arriveAt = $arriveAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
