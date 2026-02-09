<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $diagnostic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlVsio = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $medecin = null;

    #[ORM\ManyToOne(inversedBy: 'consultation')]
    private ?Ordonnance $no = null;

    #[ORM\OneToOne(mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private ?SessionVisio $sessionVisio = null;

    #[ORM\OneToOne(mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private ?SalleAttente $salleAttente = null;

    /**
     * @var Collection<int, Chat>
     */
    #[ORM\OneToMany(targetEntity: Chat::class, mappedBy: 'consultation')]
    private Collection $messages;

    #[ORM\OneToOne(mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private ?StatistiquesSession $statistiques = null;

    #[ORM\OneToOne(mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private ?Satisfaction $satisfaction = null;

    #[ORM\OneToOne(mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private ?Paiement $paiement = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(?string $diagnostic): static
    {
        $this->diagnostic = $diagnostic;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUrlVsio(): ?string
    {
        return $this->urlVsio;
    }

    public function setUrlVsio(?string $urlVsio): static
    {
        $this->urlVsio = $urlVsio;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

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

    public function getMedecin(): ?User
    {
        return $this->medecin;
    }

    public function setMedecin(?User $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getNo(): ?Ordonnance
    {
        return $this->no;
    }

    public function setNo(?Ordonnance $no): static
    {
        $this->no = $no;

        return $this;
    }

    public function getSessionVisio(): ?SessionVisio
    {
        return $this->sessionVisio;
    }

    public function setSessionVisio(SessionVisio $sessionVisio): static
    {
        // set the owning side of the relation if necessary
        if ($sessionVisio->getConsultation() !== $this) {
            $sessionVisio->setConsultation($this);
        }

        $this->sessionVisio = $sessionVisio;

        return $this;
    }

    public function getSalleAttente(): ?SalleAttente
    {
        return $this->salleAttente;
    }

    public function setSalleAttente(SalleAttente $salleAttente): static
    {
        // set the owning side of the relation if necessary
        if ($salleAttente->getConsultation() !== $this) {
            $salleAttente->setConsultation($this);
        }

        $this->salleAttente = $salleAttente;

        return $this;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Chat $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConsultation($this);
        }

        return $this;
    }

    public function removeMessage(Chat $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConsultation() === $this) {
                $message->setConsultation(null);
            }
        }

        return $this;
    }

    public function getStatistiques(): ?StatistiquesSession
    {
        return $this->statistiques;
    }

    public function setStatistiques(StatistiquesSession $statistiques): static
    {
        // set the owning side of the relation if necessary
        if ($statistiques->getConsultation() !== $this) {
            $statistiques->setConsultation($this);
        }

        $this->statistiques = $statistiques;

        return $this;
    }

    public function getSatisfaction(): ?Satisfaction
    {
        return $this->satisfaction;
    }

    public function setSatisfaction(Satisfaction $satisfaction): static
    {
        // set the owning side of the relation if necessary
        if ($satisfaction->getConsultation() !== $this) {
            $satisfaction->setConsultation($this);
        }

        $this->satisfaction = $satisfaction;

        return $this;
    }

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(Paiement $paiement): static
    {
        // set the owning side of the relation if necessary
        if ($paiement->getConsultation() !== $this) {
            $paiement->setConsultation($this);
        }

        $this->paiement = $paiement;

        return $this;
    }
}
