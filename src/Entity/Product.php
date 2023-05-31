<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'integer')]
#[ORM\DiscriminatorMap([0 => Product::class, 1 => Gpu::class, 2 => Cpu::class, 3 => Memory::class, 4 => Motherboard::class, 5 => Ssd::class, 6 => Psu::class, 7 => PCCase::class, 8 => Cooler::class])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $SKU = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $seller = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImages::class,orphanRemoval: true)]
    private Collection $productImages;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[ORM\ManyToMany(targetEntity: Locations::class, inversedBy: 'products')]
    private Collection $locations;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductInventory::class)]
    private Collection $productInventories;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uid = null;


    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CartItem::class, orphanRemoval: true)]
    private Collection $cartItems;

    #[ORM\Column(type: Types::TEXT, length:300, nullable: true)]
    private ?string $shortDesc = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Option::class)]
    private Collection $options;

    #[ORM\ManyToOne(inversedBy: 'product')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function __construct()
    {
        $this->productImages = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->productInventories = new ArrayCollection();
        $this->carts = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->options = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSKU(): ?string
    {
        return $this->SKU;
    }

    public function setSKU(?string $SKU): self
    {
        $this->SKU = $SKU;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSeller(): ?string
    {
        return $this->seller;
    }

    public function setSeller(?string $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, ProductImages>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImages $productImage): self
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }
    public function checkImgInCollection(UploadedFile $newImg): bool{
        $newImgHash=sha1_file($newImg);
        $imageFound=false;
        foreach($this->productImages as $existentImg){
            if($existentImg->getHash()==$newImgHash){
                // IF THE HASH IS THE SAME IT MEANS IMAGE ALREADY EXISTS
                $imageFound=true;
                break;
            }
        }
        return $imageFound;
    }
    public function checkImgAtIndex(ProductImages $newImg,int $elIndex): bool{
        $newImgHash=$newImg->getHash();
        $imageFound=false;
        $prodImg=$this->productImages->get($elIndex);
        if($prodImg==null)
            return $imageFound;
        #dd($prodImg);
        if($prodImg->getHash()==$newImgHash){
            // IF THE HASH IS THE SAME IT MEANS IMAGE ALREADY EXISTS
            $imageFound=true;
        }
        return $imageFound;
    }

    public function removeProductImage(ProductImages $productImage): self
    {
        if ($this->productImages->removeElement($productImage)) {
            // set the owning side to null (unless already changed)
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Locations>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Locations $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
        }

        return $this;
    }

    public function removeLocation(Locations $location): self
    {
        $this->locations->removeElement($location);

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
            $productInventory->setProduct($this);
        }

        return $this;
    }
    public function setProductInventories(Collection $productInventories): self
    {
        $this->productInventories = $productInventories;
        return $this;
    }
    public function setProductImages(Collection $productImages): self
    {
        $this->productImages = $productImages;
        return $this;
    }

    public function removeProductInventory(ProductInventory $productInventory): self
    {
        if ($this->productInventories->removeElement($productInventory)) {
            // set the owning side to null (unless already changed)
            if ($productInventory->getProduct() === $this) {
                $productInventory->setProduct(new Product());
            }
        }

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setProduct($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getProduct() === $this) {
                $cartItem->setProduct(null);
            }
        }

        return $this;
    }

    public function getShortDesc(): ?string
    {
        return $this->shortDesc;
    }

    public function setShortDesc(?string $shortDesc): self
    {
        $this->shortDesc = $shortDesc;

        return $this;
    }
    /**
     * Calculates the item total.
     *
     * @return float|int
     */
    public function getTotalInventory(): float
    {
        $total=0;
        foreach($this->getProductInventories() as $pi){
            $total+=$pi->getQuantity();
        }
        return $total;
    }
    public function getHighestInventory(): ProductInventory{
        $max_pi=null;
        $max_q=-1;
        foreach($this->getProductInventories() as $pi){
            if($pi->getQuantity()>$max_q){
                $max_q=$pi->getQuantity();
                $max_pi=$pi;
            }
        }
        return $max_pi;
    }

    /**
     * @return Collection<int, Option>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setProduct($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getProduct() === $this) {
                $option->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
