<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
#[ORM\Table(name: 'favorites')]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'favorites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $mediaType;

    #[ORM\Column(type: 'string', length: 255)]
    private string $mediaId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $mediaTitle;

    #[ORM\Column(type: 'string', length: 255)]
    private string $mediaPoster;

    #[ORM\Column(type: 'float')]
    private float $mediaRate;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): self
    {
        $this->mediaId = $mediaId;
        return $this;
    }

    public function getMediaTitle(): string
    {
        return $this->mediaTitle;
    }

    public function setMediaTitle(string $mediaTitle): self
    {
        $this->mediaTitle = $mediaTitle;
        return $this;
    }

    public function getMediaPoster(): string
    {
        return $this->mediaPoster;
    }

    public function setMediaPoster(string $mediaPoster): self
    {
        $this->mediaPoster = $mediaPoster;
        return $this;
    }

    public function getMediaRate(): float
    {
        return $this->mediaRate;
    }

    public function setMediaRate(float $mediaRate): self
    {
        $this->mediaRate = $mediaRate;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
