<?php

namespace App\Entity;

use App\Repository\ServiceDayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceDayRepository::class)]
class ServiceDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'service_day_id')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, unique: true)]
    private ?\DateTimeImmutable $serviceDate = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isOpen = true;

    #[ORM\Column(options: ['default' => 25])]
    private int $indoorCapacity = 25;

    #[ORM\Column(options: ['default' => 14])]
    private int $terraceCapacity = 14;

    #[ORM\Column(options: ['default' => 12])]
    private int $maxOnlinePartySize = 12;

    #[ORM\Column(options: ['default' => true])]
    private bool $clickCollectEnabled = true;

    #[ORM\Column(nullable: true)]
    private ?int $clickCollectOrderLimit = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceDate(): ?\DateTimeImmutable
    {
        return $this->serviceDate;
    }

    public function setServiceDate(\DateTimeImmutable $serviceDate): static
    {
        $this->serviceDate = $serviceDate;

        return $this;
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): static
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function getIndoorCapacity(): int
    {
        return $this->indoorCapacity;
    }

    public function setIndoorCapacity(int $indoorCapacity): static
    {
        $this->indoorCapacity = $indoorCapacity;

        return $this;
    }

    public function getTerraceCapacity(): int
    {
        return $this->terraceCapacity;
    }

    public function setTerraceCapacity(int $terraceCapacity): static
    {
        $this->terraceCapacity = $terraceCapacity;

        return $this;
    }

    public function getMaxOnlinePartySize(): int
    {
        return $this->maxOnlinePartySize;
    }

    public function setMaxOnlinePartySize(int $maxOnlinePartySize): static
    {
        $this->maxOnlinePartySize = $maxOnlinePartySize;

        return $this;
    }
    
    public function isClickCollectEnabled(): bool
    {
        return $this->clickCollectEnabled;
    }

    public function setClickCollectEnabled(bool $clickCollectEnabled): static
    {
        $this->clickCollectEnabled = $clickCollectEnabled;

        return $this;
    }

    public function getClickCollectOrderLimit(): ?int
    {
        return $this->clickCollectOrderLimit;
    }

    public function setClickCollectOrderLimit(?int $clickCollectOrderLimit): static
    {
        $this->clickCollectOrderLimit = $clickCollectOrderLimit;

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
}
