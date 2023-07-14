<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Order $assocOrder = null;

    #[ORM\ManyToMany(targetEntity: PCBuilderTemplate::class, mappedBy: 'cartItems')]
    private Collection $pcBuilderTemplates;

    public function __construct(){
        $this->pcBuilderTemplates = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getAssocOrder(): ?Order
    {
        return $this->assocOrder;
    }

    public function setAssocOrder(?Order $assocOrder): self
    {
        $this->assocOrder = $assocOrder;

        return $this;
    }
    /**
     * Tests if the given item given corresponds to the same order item.
     *
     * @param CartItem $item
     *
     * @return bool
     */
    public function equals(CartItem $item): bool
    {
        return $this->getProduct()->getId() === $item->getProduct()->getId();
    }
    /**
     * Calculates the item total.
     *
     * @return float|int
     */
    public function getTotal(): float
    {
        return $this->getProduct()->getPrice() * $this->getQuantity();
    }
    /**
     * Calculates the consumption total.
     *
     * @return float|int
     */
    public function getConsumptionTotal(): float
    {
        $consumptionName='consumption';
        $consumptionTotal=0;
        foreach($this->getProduct()->getOptions() as $option){
            if($option->getOptionName()==$consumptionName){
                $consumptionTotal+=$option->getOptionValue();
                break;
            }
        }
        return $consumptionTotal;
    }

    /**
     * @return Collection<int, PCBuilderTemplate>
     */
    public function getPCBuilderTemplates(): Collection
    {
        return $this->pcBuilderTemplates;
    }

    public function addPCBuilderTemplate(PCBuilderTemplate $pCBuilderTemplate): self
    {
        if (!$this->pcBuilderTemplates->contains($pCBuilderTemplate)) {
            $this->pcBuilderTemplates->add($pCBuilderTemplate);
            $pCBuilderTemplate->addProduct($this);
        }

        return $this;
    }

    public function removePCBuilderTemplate(PCBuilderTemplate $pCBuilderTemplate): self
    {
        if ($this->pcBuilderTemplates->removeElement($pCBuilderTemplate)) {
            $pCBuilderTemplate->removeProduct($this);
        }

        return $this;
    }

}
