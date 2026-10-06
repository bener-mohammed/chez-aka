<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(
        name: 'order_id',
        referencedColumnName: 'order_id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private ?CustomerOrder $customerOrder = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(
        name: 'product_id',
        referencedColumnName: 'product_id',
        nullable: false,
        onDelete: 'RESTRICT'
    )]
    private ?Product $product = null;

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getCustomerOrder(): ?CustomerOrder
    {
        return $this->customerOrder;
    }

    public function setCustomerOrder(CustomerOrder $customerOrder): static
    {
        $this->customerOrder = $customerOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
