<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'reservation_id')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $reservationTime = null;

    #[ORM\Column]
    private ?int $partySize = null;

    #[ORM\Column(length: 20)]
    private string $areaPreference = 'NO_PREFERENCE';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $confirmedArea = null;

    #[ORM\Column(length: 20)]
    private string $status = 'PENDING';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialRequest = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(
        name: 'service_day_id',
        referencedColumnName: 'service_day_id',
        nullable: false,
        onDelete: 'RESTRICT'
    )]
    private ?ServiceDay $serviceDay = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'user_id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?AppUser $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getReservationTime(): ?\DateTimeImmutable
    {
        return $this->reservationTime;
    }

    public function setReservationTime(\DateTimeImmutable $reservationTime): static
    {
        $this->reservationTime = $reservationTime;

        return $this;
    }

    public function getPartySize(): ?int
    {
        return $this->partySize;
    }

    public function setPartySize(int $partySize): static
    {
        $this->partySize = $partySize;

        return $this;
    }

    public function getAreaPreference(): string
    {
        return $this->areaPreference;
    }

    public function setAreaPreference(string $areaPreference): static
    {
        $this->areaPreference = $areaPreference;

        return $this;
    }

    public function getConfirmedArea(): ?string
    {
        return $this->confirmedArea;
    }

    public function setConfirmedArea(?string $confirmedArea): static
    {
        $this->confirmedArea = $confirmedArea;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSpecialRequest(): ?string
    {
        return $this->specialRequest;
    }

    public function setSpecialRequest(?string $specialRequest): static
    {
        $this->specialRequest = $specialRequest;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): static
    {
        $this->processedAt = $processedAt;

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

    public function getUser(): ?AppUser
    {
        return $this->user;
    }

    public function setUser(?AppUser $user): static
    {
        $this->user = $user;

        return $this;
    }
}
