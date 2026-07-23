<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ApiResource]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255)]
    private ?string $position = null;

    #[ORM\Column(length: 255)]
    private ?string $licenseNumber = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'players')]
    private Collection $parents;

    #[ORM\OneToMany(targetEntity: MatchEvent::class, mappedBy: 'player', orphanRemoval: true)]
    private Collection $matchEvents;

    #[ORM\OneToMany(targetEntity: PlayerSeasonStats::class, mappedBy: 'player', orphanRemoval: true)]
    private Collection $seasonStats;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->parents = new ArrayCollection();
        $this->matchEvents = new ArrayCollection();
        $this->seasonStats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthDate(): ?DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(string $licenseNumber): static
    {
        $this->licenseNumber = $licenseNumber;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(User $parent): static
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
            $parent->addPlayer($this);
        }

        return $this;
    }

    public function removeParent(User $parent): static
    {
        if ($this->parents->removeElement($parent)) {
            $parent->removePlayer($this);
        }

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
            $matchEvent->setPlayer($this);
        }

        return $this;
    }

    public function removeMatchEvent(MatchEvent $matchEvent): static
    {
        if ($this->matchEvents->removeElement($matchEvent)) {
            if ($matchEvent->getPlayer() === $this) {
                $matchEvent->setPlayer(null);
            }
        }

        return $this;
    }

    public function getSeasonStats(): Collection
    {
        return $this->seasonStats;
    }

    public function addSeasonStat(PlayerSeasonStats $seasonStat): static
    {
        if (!$this->seasonStats->contains($seasonStat)) {
            $this->seasonStats->add($seasonStat);
            $seasonStat->setPlayer($this);
        }

        return $this;
    }

    public function removeSeasonStat(PlayerSeasonStats $seasonStat): static
    {
        if ($this->seasonStats->removeElement($seasonStat)) {
            if ($seasonStat->getPlayer() === $this) {
                $seasonStat->setPlayer(null);
            }
        }

        return $this;
    }
}
