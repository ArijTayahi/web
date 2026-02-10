<?php

namespace App\Entity;

use App\Repository\DoctorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctorRepository::class)]
#[ORM\Table(name: 'doctors')]
class Doctor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'doctor')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $licenseCode = null;

    #[ORM\Column(options: ['default' => 0])]
    private bool $isCertified = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, DoctorDocument>
     */
    #[ORM\OneToMany(mappedBy: 'doctor', targetEntity: DoctorDocument::class, cascade: ['persist', 'remove'])]
    private Collection $documents;

    /**
     * @var Collection<int, Consultation>
     */
    #[ORM\OneToMany(mappedBy: 'doctor', targetEntity: Consultation::class)]
    private Collection $consultations;

    /**
     * @var Collection<int, Ordonnance>
     */
    #[ORM\OneToMany(mappedBy: 'doctor', targetEntity: Ordonnance::class)]
    private Collection $ordonnances;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
        $this->consultations = new ArrayCollection();
        $this->ordonnances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLicenseCode(): ?string
    {
        return $this->licenseCode;
    }

    public function setLicenseCode(?string $licenseCode): self
    {
        $this->licenseCode = $licenseCode;

        return $this;
    }

    public function isCertified(): bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(bool $isCertified): self
    {
        $this->isCertified = $isCertified;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, DoctorDocument>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(DoctorDocument $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setDoctor($this);
        }

        return $this;
    }

    public function removeDocument(DoctorDocument $document): self
    {
        if ($this->documents->removeElement($document)) {
            /** @var Doctor $doctor */
            $doctor = $document->getDoctor();
            if ($doctor === $this) {
                // DoctorDocument doctor is not nullable
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    public function addConsultation(Consultation $consultation): self
    {
        if (!$this->consultations->contains($consultation)) {
            $this->consultations->add($consultation);
            $consultation->setDoctor($this);
        }

        return $this;
    }

    public function removeConsultation(Consultation $consultation): self
    {
        if ($this->consultations->removeElement($consultation)) {
            $doctor = $consultation->getDoctor();
            if ($doctor !== null && $doctor === $this) {
                $consultation->setDoctor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ordonnance>
     */
    public function getOrdonnances(): Collection
    {
        return $this->ordonnances;
    }

    public function addOrdonnance(Ordonnance $ordonnance): self
    {
        if (!$this->ordonnances->contains($ordonnance)) {
            $this->ordonnances->add($ordonnance);
            $ordonnance->setDoctor($this);
        }

        return $this;
    }

    public function removeOrdonnance(Ordonnance $ordonnance): self
    {
        if ($this->ordonnances->removeElement($ordonnance)) {
            // set the owning side to null (unless already changed)
            /** @var Doctor $doctor */
            $doctor = $ordonnance->getDoctor();
            if ($doctor === $this) {
                // Ordonnance doctor is not nullable, no need to set to null
            }
        }

        return $this;
    }
}
