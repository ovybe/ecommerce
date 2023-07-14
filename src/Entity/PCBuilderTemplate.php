<?php

namespace App\Entity;

use App\Repository\PCBuilderTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PCBuilderTemplateRepository::class)]
class PCBuilderTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $templateName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $templateDescription = null;

    #[ORM\ManyToMany(targetEntity: CartItem::class, inversedBy: 'pcBuilderTemplates')]
    private Collection $cartItems;

    #[ORM\ManyToOne(inversedBy: 'pcBuilderTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owningUser = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uid = null;


    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getTemplateDescription(): ?string
    {
        return $this->templateDescription;
    }

    public function setTemplateDescription(?string $templateDescription): self
    {
        $this->templateDescription = $templateDescription;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        $this->cartItems->removeElement($cartItem);

        return $this;
    }

    public function getOwningUser(): ?User
    {
        return $this->owningUser;
    }

    public function setOwningUser(?User $owningUser): self
    {
        $this->owningUser = $owningUser;

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
     * Calculates the order total.
     *
     * @return float
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getCartItems() as $item) {
            $total += $item->getTotal();
        }

        return $total;
    }
    public function getConsumptionTotal(): float
    {
        $total = 0;

        foreach ($this->getCartItems() as $item) {
            $total += $item->getConsumptionTotal();
        }

        return $total;
    }
    public function getPSU(): ?CartItem
    {
        $psu_name='psu';
        foreach ($this->getCartItems() as $item) {
            if($item->getProduct()->getCategory()->getCategoryName()==$psu_name){
                return $item;
            };
        }

        return null;
    }
    public function getTotalScore(): float{
        $score=0;
        $items_count=0;
        foreach ($this->getCartItems() as $item){
            $score+=$item->getProduct()->getScore();
            $items_count++;
        }
        $score/=$items_count;
        return $score;
    }
}
