<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PlayerSeasonStatsRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: PlayerSeasonStatsRepository::class)]
#[ApiResource]
class PlayerSeasonStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $season = null;

    #[ORM\Column]
    private ?int $goals = 0;

    #[ORM\Column]
    private ?int $keyPasses = 0;

    #[ORM\Column]
    private ?int $minutesPlayed = 0;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'seasonStats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    public function __construct()
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGoals(): ?int
    {
        return $this->goals;
    }

    public function setGoals(int $goals): static
    {
        $this->goals = $goals;

        return $this;
    }

    public function getKeyPasses(): ?int
    {
        return $this->keyPasses;
    }

    public function setKeyPasses(int $keyPasses): static
    {
        $this->keyPasses = $keyPasses;

        return $this;
    }

    public function getMinutesPlayed(): ?int
    {
        return $this->minutesPlayed;
    }

    public function setMinutesPlayed(int $minutesPlayed): static
    {
        $this->minutesPlayed = $minutesPlayed;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }
}
