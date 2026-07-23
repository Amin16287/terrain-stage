<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(enumType: UserRole::class)]
    private ?UserRole $role = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $club = null;

    #[ORM\ManyToOne(inversedBy: 'coaches')]
    private ?Team $team = null;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'parents')]
    private Collection $players;

    #[ORM\OneToMany(targetEntity: MatchEvent::class, mappedBy: 'taggedBy', orphanRemoval: true)]
    private Collection $matchEvents;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->players = new ArrayCollection();
        $this->matchEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_' . strtoupper($this->role->value)];
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getMatchEvents(): Collection
    {
        return $this->matchEvents;
    }

    public function addMatchEvent(MatchEvent $matchEvent): static
    {
        if (!$this->matchEvents->contains($matchEvent)) {
            $this->matchEvents->add($matchEvent);
            $matchEvent->setTaggedBy($this);
        }

        return $this;
    }

    public function removeMatchEvent(MatchEvent $matchEvent): static
    {
        if ($this->matchEvents->removeElement($matchEvent)) {
            if ($matchEvent->getTaggedBy() === $this) {
                $matchEvent->setTaggedBy(null);
            }
        }

        return $this;
    }
}
