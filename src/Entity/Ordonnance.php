<?php

namespace App\Entity;

use App\Repository\OrdonnanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdonnanceRepository::class)]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $numeroOrdonnance = null;

    #[ORM\Column]
    private ?\DateTime $dateEmission = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateValidite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $qrCode = null;

    /**
     * @var Collection<int, Consultation>
     */
    #[ORM\OneToMany(targetEntity: Consultation::class, mappedBy: 'no')]
    private Collection $consultation;

    /**
     * @var Collection<int, LigneOrdonnance>
     */
    #[ORM\OneToMany(targetEntity: LigneOrdonnance::class, mappedBy: 'ordonnance')]
    private Collection $Lignes;

    #[ORM\OneToOne(mappedBy: 'ordonnance', cascade: ['persist', 'remove'])]
    private ?Facture $facture = null;

    public function __construct()
    {
        $this->consultation = new ArrayCollection();
        $this->Lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroOrdonnance(): ?string
    {
        return $this->numeroOrdonnance;
    }

    public function setNumeroOrdonnance(string $numeroOrdonnance): static
    {
        $this->numeroOrdonnance = $numeroOrdonnance;

        return $this;
    }

    public function getDateEmission(): ?\DateTime
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTime $dateEmission): static
    {
        $this->dateEmission = $dateEmission;

        return $this;
    }

    public function getDateValidite(): ?\DateTime
    {
        return $this->dateValidite;
    }

    public function setDateValidite(?\DateTime $dateValidite): static
    {
        $this->dateValidite = $dateValidite;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): static
    {
        $this->qrCode = $qrCode;

        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getConsultation(): Collection
    {
        return $this->consultation;
    }

    public function addConsultation(Consultation $consultation): static
    {
        if (!$this->consultation->contains($consultation)) {
            $this->consultation->add($consultation);
            $consultation->setNo($this);
        }

        return $this;
    }

    public function removeConsultation(Consultation $consultation): static
    {
        if ($this->consultation->removeElement($consultation)) {
            // set the owning side to null (unless already changed)
            if ($consultation->getNo() === $this) {
                $consultation->setNo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LigneOrdonnance>
     */
    public function getLignes(): Collection
    {
        return $this->Lignes;
    }

    public function addLigne(LigneOrdonnance $ligne): static
    {
        if (!$this->Lignes->contains($ligne)) {
            $this->Lignes->add($ligne);
            $ligne->setOrdonnance($this);
        }

        return $this;
    }

    public function removeLigne(LigneOrdonnance $ligne): static
    {
        if ($this->Lignes->removeElement($ligne)) {
            // set the owning side to null (unless already changed)
            if ($ligne->getOrdonnance() === $this) {
                $ligne->setOrdonnance(null);
            }
        }

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        // unset the owning side of the relation if necessary
        if ($facture === null && $this->facture !== null) {
            $this->facture->setOrdonnance(null);
        }

        // set the owning side of the relation if necessary
        if ($facture !== null && $facture->getOrdonnance() !== $this) {
            $facture->setOrdonnance($this);
        }

        $this->facture = $facture;

        return $this;
    }
}
