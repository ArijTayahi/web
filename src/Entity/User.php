<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Entity\RendezVous;
use App\Entity\Availability;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_users_email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uniq_users_username', columns: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 80)]
    private string $username;

    #[ORM\Column]
    private string $password;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(options: ['default' => 1])]
    private bool $isActive = true;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_roles')]
    private Collection $roles;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Doctor::class, cascade: ['persist', 'remove'])]
    private ?Doctor $doctor = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Patient::class, cascade: ['persist', 'remove'])]
    private ?Patient $patient = null;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'doctor')]
    private Collection $rendezVousAsDoctor;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'patient')]
    private Collection $rendezVousAsPatient;

    /**
     * @var Collection<int, Availability>
     */
    #[ORM\OneToMany(targetEntity: Availability::class, mappedBy: 'doctor')]
    private Collection $availabilities;

    /**
     * @var Collection<int, Availability>
     */
    #[ORM\OneToMany(targetEntity: Availability::class, mappedBy: 'doctor')]
    private Collection $no;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->rendezVousAsDoctor = new ArrayCollection();
        $this->rendezVousAsPatient = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
        $this->no = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        foreach ($this->roles as $role) {
            $roles[] = $role->getName();
        }

        return array_values(array_unique($roles));
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function addRoleEntity(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addUser($this);
        }

        return $this;
    }

    public function removeRoleEntity(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removeUser($this);
        }

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getDoctor(): ?Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(?Doctor $doctor): self
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVousAsDoctor(): Collection
    {
        return $this->rendezVousAsDoctor;
    }

    public function addRendezVousAsDoctor(RendezVous $rendezVousAsDoctor): static
    {
        if (!$this->rendezVousAsDoctor->contains($rendezVousAsDoctor)) {
            $this->rendezVousAsDoctor->add($rendezVousAsDoctor);
            $rendezVousAsDoctor->setDoctor($this);
        }

        return $this;
    }

    public function removeRendezVousAsDoctor(RendezVous $rendezVousAsDoctor): static
    {
        if ($this->rendezVousAsDoctor->removeElement($rendezVousAsDoctor)) {
            // set the owning side to null (unless already changed)
            if ($rendezVousAsDoctor->getDoctor() === $this) {
                $rendezVousAsDoctor->setDoctor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVousAsPatient(): Collection
    {
        return $this->rendezVousAsPatient;
    }

    public function addRendezVousAsPatient(RendezVous $rendezVousAsPatient): static
    {
        if (!$this->rendezVousAsPatient->contains($rendezVousAsPatient)) {
            $this->rendezVousAsPatient->add($rendezVousAsPatient);
            $rendezVousAsPatient->setPatient($this);
        }

        return $this;
    }

    public function removeRendezVousAsPatient(RendezVous $rendezVousAsPatient): static
    {
        if ($this->rendezVousAsPatient->removeElement($rendezVousAsPatient)) {
            // set the owning side to null (unless already changed)
            if ($rendezVousAsPatient->getPatient() === $this) {
                $rendezVousAsPatient->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Availability>
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setDoctor($this);
        }

        return $this;
    }

    public function removeAvailability(Availability $availability): static
    {
        if ($this->availabilities->removeElement($availability)) {
            // set the owning side to null (unless already changed)
            if ($availability->getDoctor() === $this) {
                $availability->setDoctor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Availability>
     */
    public function getNo(): Collection
    {
        return $this->no;
    }

    public function addNo(Availability $no): static
    {
        if (!$this->no->contains($no)) {
            $this->no->add($no);
            $no->setDoctor($this);
        }

        return $this;
    }

    public function removeNo(Availability $no): static
    {
        if ($this->no->removeElement($no)) {
            // set the owning side to null (unless already changed)
            if ($no->getDoctor() === $this) {
                $no->setDoctor(null);
            }
        }

        return $this;
    }
}
