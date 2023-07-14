<?php

namespace App\Entity;

use App\Repository\InventoryLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryLogRepository::class)]
class InventoryLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $loggedOrder = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductInventory $loggedInv = null;

    #[ORM\Column]
    private ?int $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLoggedOrder(): ?Order
    {
        return $this->loggedOrder;
    }

    public function setLoggedOrder(?Order $loggedOrder): self
    {
        $this->loggedOrder = $loggedOrder;

        return $this;
    }

    public function getLoggedInv(): ?ProductInventory
    {
        return $this->loggedInv;
    }

    public function setLoggedInv(?ProductInventory $loggedInv): self
    {
        $this->loggedInv = $loggedInv;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
