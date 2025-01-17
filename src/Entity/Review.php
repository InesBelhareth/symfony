<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'review')]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $mediaId = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $mediaType = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $mediaTitle = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $mediaPoster = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): self
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getMediaTitle(): ?string
    {
        return $this->mediaTitle;
    }

    public function setMediaTitle(string $mediaTitle): self
    {
        $this->mediaTitle = $mediaTitle;

        return $this;
    }

    public function getMediaPoster(): ?string
    {
        return $this->mediaPoster;
    }

    public function setMediaPoster(string $mediaPoster): self
    {
        $this->mediaPoster = $mediaPoster;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function getUser(): ?User
{
    return $this->user;
}

public function setUser(?User $user): self
{
    $this->user = $user;

    return $this;
}

}
