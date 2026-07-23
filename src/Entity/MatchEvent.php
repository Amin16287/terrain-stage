<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\EventType;
use App\Enum\SyncStatus;
use App\Repository\MatchEventRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: MatchEventRepository::class)]
#[ApiResource]
class MatchEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: EventType::class)]
    private ?EventType $type = null;

    #[ORM\Column]
    private ?int $minute = null;

    #[ORM\Column(type: 'float')]
    private ?float $zoneX = null;

    #[ORM\Column(type: 'float')]
    private ?float $zoneY = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?int $relatedPlayerId = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(enumType: SyncStatus::class)]
    private ?SyncStatus $syncStatus = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $clientUuid = null;

    #[ORM\ManyToOne(inversedBy: 'matchEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?GameMatch $match = null;


    #[ORM\ManyToOne(inversedBy: 'matchEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'matchEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $taggedBy = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->syncStatus = SyncStatus::PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(EventType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMinute(): ?int
    {
        return $this->minute;
    }

    public function setMinute(int $minute): static
    {
        $this->minute = $minute;

        return $this;
    }

    public function getZoneX(): ?float
    {
        return $this->zoneX;
    }

    public function setZoneX(float $zoneX): static
    {
        $this->zoneX = $zoneX;

        return $this;
    }

    public function getZoneY(): ?float
    {
        return $this->zoneY;
    }

    public function setZoneY(float $zoneY): static
    {
        $this->zoneY = $zoneY;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getRelatedPlayerId(): ?int
    {
        return $this->relatedPlayerId;
    }

    public function setRelatedPlayerId(?int $relatedPlayerId): static
    {
        $this->relatedPlayerId = $relatedPlayerId;

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

    public function getSyncStatus(): ?SyncStatus
    {
        return $this->syncStatus;
    }

    public function setSyncStatus(SyncStatus $syncStatus): static
    {
        $this->syncStatus = $syncStatus;

        return $this;
    }

    public function getClientUuid(): ?string
    {
        return $this->clientUuid;
    }

    public function setClientUuid(string $clientUuid): static
    {
        $this->clientUuid = $clientUuid;

        return $this;
    }

    public function getMatch(): ?GameMatch
    {
        return $this->match;
    }

    public function setMatch(?GameMatch $match): static
    {
        $this->match = $match;

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

    public function getTaggedBy(): ?User
    {
        return $this->taggedBy;
    }

    public function setTaggedBy(?User $taggedBy): static
    {
        $this->taggedBy = $taggedBy;

        return $this;
    }
}
