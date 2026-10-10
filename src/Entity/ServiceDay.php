<?php

namespace App\Entity;

use App\Repository\ServiceDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['serviceDate'],
    message: 'Un jour de service existe déjà pour cette date.'
)]

#[ORM\Entity(repositoryClass: ServiceDayRepository::class)]
class ServiceDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'service_day_id')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, unique: true)]
    #[Assert\NotNull(message: 'La date du service est obligatoire.')]
    private ?\DateTimeImmutable $serviceDate = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isOpen = true;

    #[ORM\Column(options: ['default' => 25])]
    #[Assert\Range(
        min: 0,
        max: 25,
        notInRangeMessage: 'La capacité intérieure doit être comprise entre {{ min }} et {{ max }} personnes.'
    )]
    private int $indoorCapacity = 25;

    #[ORM\Column(options: ['default' => 14])]
    #[Assert\Range(
        min: 0,
        max: 14,
        notInRangeMessage: 'La capacité terrasse doit être comprise entre {{ min }} et {{ max }} personnes.'
    )]
    private int $terraceCapacity = 14;

    #[ORM\Column(options: ['default' => 12])]
    #[Assert\Range(
        min: 1,
        max: 12,
        notInRangeMessage: 'Une réservation en ligne doit être limitée entre {{ min }} et {{ max }} personnes.'
    )]
    private int $maxOnlinePartySize = 12;

    #[ORM\Column(options: ['default' => true])]
    private bool $clickCollectEnabled = true;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(
        message: 'La limite de commandes doit être supérieure à 0.'
    )]
    private ?int $clickCollectOrderLimit = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'serviceDay')]
    private Collection $reservations;

    /**
     * @var Collection<int, CustomerOrder>
     */
    #[ORM\OneToMany(targetEntity: CustomerOrder::class, mappedBy: 'serviceDay')]
    private Collection $customerOrders;

    #[ORM\OneToOne(mappedBy: 'serviceDay')]
    private ?RestaurantEvent $restaurantEvent = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reservations = new ArrayCollection();
        $this->customerOrders = new ArrayCollection();
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

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setServiceDay($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomerOrder>
     */
    public function getCustomerOrders(): Collection
    {
        return $this->customerOrders;
    }

    public function addCustomerOrder(CustomerOrder $customerOrder): static
    {
        if (!$this->customerOrders->contains($customerOrder)) {
            $this->customerOrders->add($customerOrder);
            $customerOrder->setServiceDay($this);
        }

        return $this;
    }

    public function getRestaurantEvent(): ?RestaurantEvent
    {
        return $this->restaurantEvent;
    }

    public function setRestaurantEvent(RestaurantEvent $restaurantEvent): static
    {
        // set the owning side of the relation if necessary
        if ($restaurantEvent->getServiceDay() !== $this) {
            $restaurantEvent->setServiceDay($this);
        }

        $this->restaurantEvent = $restaurantEvent;

        return $this;
    }
}
