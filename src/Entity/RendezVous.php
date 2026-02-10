<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $appointmentDateTime = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(length: 20, enumType: StatusRDVEnum::class)]
    private ?StatusRDVEnum $status = null;

    #[ORM\Column(length: 20, enumType: ConsultationTypeEnum::class)]
    private ?ConsultationTypeEnum $consultationType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?bool $reminderSent = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?float $cancellationRisk = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVousAsDoctor')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $doctor = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVousAsPatient')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointmentDateTime(): ?\DateTime
    {
        return $this->appointmentDateTime;
    }

    public function setAppointmentDateTime(\DateTime $appointmentDateTime): static
    {
        $this->appointmentDateTime = $appointmentDateTime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getStatus(): ?StatusRDVEnum
    {
        return $this->status;
    }

    public function setStatus(StatusRDVEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getConsultationType(): ?ConsultationTypeEnum
    {
        return $this->consultationType;
    }

    public function setConsultationType(ConsultationTypeEnum $consultationType): static
    {
        $this->consultationType = $consultationType;
        return $this;
    }
    

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function isReminderSent(): ?bool
    {
        return $this->reminderSent;
    }

    public function setReminderSent(bool $reminderSent): static
    {
        $this->reminderSent = $reminderSent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCancellationRisk(): ?float
    {
        return $this->cancellationRisk;
    }

    public function setCancellationRisk(?float $cancellationRisk): static
    {
        $this->cancellationRisk = $cancellationRisk;

        return $this;
    }

    public function getDoctor(): ?User
    {
        return $this->doctor;
    }

    public function setDoctor(?User $doctor): static
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

        return $this;
    }
}
