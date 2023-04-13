<?php

namespace App\Entity;

use App\Repository\LocationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationsRepository::class)]
class Locations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'locations')]
    private Collection $products;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: ProductInventory::class)]
    private Collection $productInventories;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 8, nullable: true)]
    private ?string $coordX = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    private ?string $coordY = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->productInventories = $this->productInventories ?: new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addLocation($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            $product->removeLocation($this);
        }

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, ProductInventory>
     */
    public function getProductInventories(): Collection
    {
        return $this->productInventories;
    }

    public function addProductInventory(ProductInventory $productInventory): self
    {
        if (!$this->productInventories->contains($productInventory)) {
            $this->productInventories->add($productInventory);
            $productInventory->setLocation($this);
        }

        return $this;
    }

    public function removeProductInventory(ProductInventory $productInventory): self
    {
        if ($this->productInventories->removeElement($productInventory)) {
            // set the owning side to null (unless already changed)
            if ($productInventory->getLocation() === $this) {
                $productInventory->setLocation(null);
            }
        }

        return $this;
    }

    public function getCoordX(): ?string
    {
        return $this->coordX;
    }

    public function setCoordX(?string $coordX): self
    {
        $this->coordX = $coordX;

        return $this;
    }

    public function getCoordY(): ?string
    {
        return $this->coordY;
    }

    public function setCoordY(?string $coordY): self
    {
        $this->coordY = $coordY;

        return $this;
    }
}
