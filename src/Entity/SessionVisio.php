<?php

namespace App\Entity;

use App\Repository\SessionVisioRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionVisioRepository::class)]
class SessionVisio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $roomId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

  #[ORM\Column(nullable: true)]
private ?\DateTime $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $endedAt = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\OneToOne(inversedBy: 'sessionVisio', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $consultation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomId(): ?string
    {
        return $this->roomId;
    }

    public function setRoomId(string $roomId): static
    {
        $this->roomId = $roomId;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreateAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTime $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTime
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTime $endedAt): static
    {
        $this->endedAt = $endedAt;

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
