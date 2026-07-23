<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ApiResource]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private ?string $season = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $club = null;

    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'team', orphanRemoval: true)]
    private Collection $players;

    #[ORM\OneToMany(targetEntity: \App\Entity\Match::class, mappedBy: 'homeTeam', orphanRemoval: true)]
    private Collection $homeMatches;

    #[ORM\OneToMany(targetEntity: GameMatch::class, mappedBy: 'awayTeam')]
    private Collection $awayMatches;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'team')]
    private Collection $coaches;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->players = new ArrayCollection();
        $this->homeMatches = new ArrayCollection();
        $this->awayMatches = new ArrayCollection();
        $this->coaches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(string $season): static
    {
        $this->season = $season;

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

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setTeam($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }

        return $this;
    }

    public function getHomeMatches(): Collection
    {
        return $this->homeMatches;
    }

    public function addHomeMatch(GameMatch $homeMatch): static
    {
        if (!$this->homeMatches->contains($homeMatch)) {
            $this->homeMatches->add($homeMatch);
            $homeMatch->setHomeTeam($this);
        }

        return $this;
    }

    public function removeHomeMatch(GameMatch $homeGameMatch): static
    {
        if ($this->homeMatches->removeElement($homeMatch)) {
            if ($homeMatch->getHomeTeam() === $this) {
                $homeMatch->setHomeTeam(null);
            }
        }

        return $this;
    }

    public function getAwayMatches(): Collection
    {
        return $this->awayMatches;
    }

    public function addAwayMatch(GameMatch $awayMatch): static
    {
        if (!$this->awayMatches->contains($awayMatch)) {
            $this->awayMatches->add($awayMatch);
            $awayMatch->setAwayTeam($this);
        }

        return $this;
    }

    public function removeAwayMatch(GameMatch $awayMatch): static
    {
        if ($this->awayMatches->removeElement($awayMatch)) {
            if ($awayMatch->getAwayTeam() === $this) {
                $awayMatch->setAwayTeam(null);
            }
        }

        return $this;
    }

    public function getCoaches(): Collection
    {
        return $this->coaches;
    }

    public function addCoach(User $coach): static
    {
        if (!$this->coaches->contains($coach)) {
            $this->coaches->add($coach);
            $coach->setTeam($this);
        }

        return $this;
    }

    public function removeCoach(User $coach): static
    {
        if ($this->coaches->removeElement($coach)) {
            if ($coach->getTeam() === $this) {
                $coach->setTeam(null);
            }
        }

        return $this;
    }
}
