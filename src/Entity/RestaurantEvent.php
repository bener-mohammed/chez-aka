<?php

namespace App\Entity;

use App\Repository\RestaurantEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: RestaurantEventRepository::class)]
#[UniqueEntity(
    fields: ['serviceDay'],
    message: 'Un événement existe déjà pour ce jour de service.'
)]
class RestaurantEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'event_id')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le titre de l’événement est obligatoire.')]
    #[Assert\Length(
        max: 150,
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 3000,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La date et l’heure de début sont obligatoires.')]
    private ?\DateTimeImmutable $startDatetime = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThan(
        propertyPath: 'startDatetime',
        message: 'La fin de l’événement doit être postérieure au début.'
    )]
    private ?\DateTimeImmutable $endDatetime = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
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
    #[Assert\NotNull(message: 'Le jour de service est obligatoire.')]
    private ?ServiceDay $serviceDay = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[Assert\Callback]
    public function validateEventDate(
        ExecutionContextInterface $context
    ): void {
        if (
            $this->serviceDay === null
            || $this->serviceDay->getServiceDate() === null
            || $this->startDatetime === null
        ) {
            return;
        }

        if (
            $this->serviceDay->getServiceDate()->format('Y-m-d')
            !== $this->startDatetime->format('Y-m-d')
        ) {
            $context
                ->buildViolation(
                    'Le début de l’événement doit être le même jour que le jour de service sélectionné.'
                )
                ->atPath('startDatetime')
                ->addViolation();
        }
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
