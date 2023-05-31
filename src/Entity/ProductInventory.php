<?php

namespace App\Entity;

use App\Repository\ProductInventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductInventoryRepository::class)]
class ProductInventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'productInventories')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productInventories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Locations $location = null;

    #[ORM\OneToMany(mappedBy: 'loggedInv', targetEntity: InventoryLog::class, orphanRemoval: true)]
    private Collection $inventoryLogs;

    public function __construct()
    {
        $this->inventoryLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getLocation(): ?Locations
    {
        return $this->location;
    }

    public function setLocation(?Locations $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, InventoryLog>
     */
    public function getInventoryLogs(): Collection
    {
        return $this->inventoryLogs;
    }

    public function addInventoryLog(InventoryLog $inventoryLog): self
    {
        if (!$this->inventoryLogs->contains($inventoryLog)) {
            $this->inventoryLogs->add($inventoryLog);
            $inventoryLog->setLoggedInv($this);
        }

        return $this;
    }

    public function removeInventoryLog(InventoryLog $inventoryLog): self
    {
        if ($this->inventoryLogs->removeElement($inventoryLog)) {
            // set the owning side to null (unless already changed)
            if ($inventoryLog->getLoggedInv() === $this) {
                $inventoryLog->setLoggedInv(null);
            }
        }

        return $this;
    }

}
