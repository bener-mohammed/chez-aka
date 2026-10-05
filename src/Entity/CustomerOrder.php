<?php

namespace App\Entity;

use App\Repository\CustomerOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerOrderRepository::class)]
class CustomerOrder
{
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(name: 'order_id')]
private ?int $id = null;

#[ORM\Column(length: 30, unique: true)]
private ?string $orderNumber = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $pickupTime = null;

#[ORM\Column(length: 30)]
private string $status = 'PENDING_PAYMENT';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalAmount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $specialRequest = null;

#[ORM\Column]
private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

#[ORM\ManyToOne(inversedBy: 'customerOrders')]
#[ORM\JoinColumn(
    name: 'service_day_id',
    referencedColumnName: 'service_day_id',
    nullable: false,
    onDelete: 'RESTRICT'
)]
private ?ServiceDay $serviceDay = null;

#[ORM\ManyToOne(inversedBy: 'customerOrders')]
#[ORM\JoinColumn(
    name: 'user_id',
    referencedColumnName: 'user_id',
    nullable: false,
    onDelete: 'RESTRICT'
)]
private ?AppUser $user = null;

/**
 * @var Collection<int, Payment>
 */
#[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'customerOrder')]
private Collection $payments;

    public function __construct()
{
    $this->createdAt = new \DateTimeImmutable();
    $this->payments = new ArrayCollection();
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getPickupTime(): ?\DateTimeImmutable
    {
        return $this->pickupTime;
    }

    public function setPickupTime(\DateTimeImmutable $pickupTime): static
    {
        $this->pickupTime = $pickupTime;

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

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

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

    public function getUser(): ?AppUser
    {
        return $this->user;
    }

    public function setUser(AppUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setCustomerOrder($this);
        }

        return $this;
    }
}
