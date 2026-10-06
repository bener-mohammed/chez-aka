<?php

namespace App\Entity;

use App\Repository\RestaurantEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantEventRepository::class)]
class RestaurantEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'event_id')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDatetime = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDatetime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'restaurantEvent')]
    #[ORM\JoinColumn(
        name: 'service_day_id',
        referencedColumnName: 'service_day_id',
        nullable: false,
        unique: true,
        onDelete: 'RESTRICT'
    )]
    private ?ServiceDay $serviceDay = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDatetime(): ?\DateTimeImmutable
    {
        return $this->startDatetime;
    }

    public function setStartDatetime(\DateTimeImmutable $startDatetime): static
    {
        $this->startDatetime = $startDatetime;

        return $this;
    }

    public function getEndDatetime(): ?\DateTimeImmutable
    {
        return $this->endDatetime;
    }

    public function setEndDatetime(?\DateTimeImmutable $endDatetime): static
    {
        $this->endDatetime = $endDatetime;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getServiceDay(): ?ServiceDay
    {
        return $this->serviceDay;
    }

    public function setServiceDay(ServiceDay $serviceDay): static
    {
        $this->serviceDay = $serviceDay;

        return $this;
    }
}
