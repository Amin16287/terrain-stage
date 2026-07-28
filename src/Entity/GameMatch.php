<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\MatchStatus;
use App\Repository\GameMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameMatchRepository::class)]
#[ORM\Table(name: '`match`')] // keep because MATCH is SQL reserved
#[ApiResource]
class GameMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $venue = null;

    #[ORM\Column(enumType: MatchStatus::class)]
    private ?MatchStatus $status = null;

    #[ORM\Column]
    private int $scoreHome = 0;

    #[ORM\Column(nullable: true)]
    private ?int $scoreAway = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $opponentName = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'homeMatches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $homeTeam = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'awayMatches')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Team $awayTeam = null;

    #[ORM\OneToMany(
        mappedBy: 'match',
        targetEntity: MatchEvent::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $matchEvents;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->matchEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(string $venue): static
    {
        $this->venue = $venue;
        return $this;
    }

    public function getStatus(): ?MatchStatus
    {
        return $this->status;
    }

    public function setStatus(MatchStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getScoreHome(): int
    {
        return $this->scoreHome;
    }

    public function setScoreHome(int $scoreHome): static
    {
        $this->scoreHome = $scoreHome;
        return $this;
    }

    public function getScoreAway(): ?int
    {
        return $this->scoreAway;
    }

    public function setScoreAway(?int $scoreAway): static
    {
        $this->scoreAway = $scoreAway;
        return $this;
    }

    public function getOpponentName(): ?string
    {
        return $this->opponentName;
    }

    public function setOpponentName(?string $opponentName): static
    {
        $this->opponentName = $opponentName;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getHomeTeam(): ?Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(?Team $homeTeam): static
    {
        $this->homeTeam = $homeTeam;
        return $this;
    }

    public function getAwayTeam(): ?Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(?Team $awayTeam): static
    {
        $this->awayTeam = $awayTeam;
        return $this;
    }

    /**
     * @return Collection<int, MatchEvent>
     */
    public function getMatchEvents(): Collection
    {
        return $this->matchEvents;
    }

    public function addMatchEvent(MatchEvent $matchEvent): static
    {
        if (!$this->matchEvents->contains($matchEvent)) {
            $this->matchEvents->add($matchEvent);
            $matchEvent->setMatch($this);
        }

        return $this;
    }

    public function removeMatchEvent(MatchEvent $matchEvent): static
    {
        if ($this->matchEvents->removeElement($matchEvent)) {
            if ($matchEvent->getMatch() === $this) {
                $matchEvent->setMatch(null);
            }
        }

        return $this;
    }
}
