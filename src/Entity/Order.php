<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Stripe\Checkout\Session;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    const STATUS_CART = false;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'order_cart', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'assocOrder', targetEntity: CartItem::class, orphanRemoval: true, cascade:['persist'])]
    private Collection $items;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?PaymentDetail $paymentDetail = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $stripe_session = null;

    #[ORM\OneToMany(mappedBy: 'loggedOrder', targetEntity: InventoryLog::class, orphanRemoval: true)]
    private Collection $inventoryLogs;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Cities $city = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Countries $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 17, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customerName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $session_expiration = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $paymentType = null;

    #[ORM\Column(nullable:true)]
    private ?bool $paid = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Discount $discount = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->inventoryLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItem $item): self
    {
        foreach($this->items as $existingItem){
            if ($existingItem->equals($item)) {
                $existingItem->setQuantity(
                    $existingItem->getQuantity() + $item->getQuantity()
                );
                return $this;
            }
        }
        $this->items->add($item);
        $item->setAssocOrder($this);


        return $this;
    }

    public function removeItem(CartItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getAssocOrder() === $this) {
                $item->setAssocOrder(null);
            }
        }

        return $this;
    }
    /**
     * Removes all items from the order.
     *
     * @return $this
     */
    public function removeItems(): self
    {
        foreach ($this->getItems() as $item) {
            $this->removeItem($item);
        }

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

        foreach ($this->getItems() as $item) {
            $total += $item->getTotal();
        }

        return $total;
    }

    /**
     * Calculates the order total with discount.
     *
     * @return float
     */
    public function getTotalWithDiscount(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item->getTotal();
        }

        if($this->getDiscount()!=null){
            $discountAmount=$this->getDiscount()->getDiscountAmount($total);
            $total-=$discountAmount;
        }

        return $total;
    }

    public function getPaymentDetail(): ?PaymentDetail
    {
        return $this->paymentDetail;
    }

    public function setPaymentDetail(?PaymentDetail $paymentDetail): self
    {
        $this->paymentDetail = $paymentDetail;

        return $this;
    }

    public function getStripeSession(): ?string
    {
        return $this->stripe_session;
    }

    public function setStripeSession(string $stripe_session=null): self
    {
        $this->stripe_session = $stripe_session;

        return $this;
    }

    public function removeInventory(EntityManagerInterface $entityManager): self{
        foreach($this->getItems() as $item){
            $pi=$item->getProduct()->getHighestInventory();
            $loggedPI=new InventoryLog();
            $itemQuantity=$item->getQuantity();
            $remainingQuantity=$pi->getQuantity()-$itemQuantity;
            $loggedPI->setAmount($itemQuantity);
            $loggedPI->setLoggedInv($pi);
            $loggedPI->setLoggedOrder($this);
            $entityManager->persist($loggedPI);
            $pi->setQuantity($remainingQuantity);
        }
        $entityManager->flush();
        return $this;
    }
    public function returnInventory(EntityManagerInterface $entityManager): self{
        // TODO: ADD NEW LOCATION ENTITY WITH AMOUNT OF "DEBT" FROM EACH ORDER
        // TODO: Add an order ID (maybe use the invoice id)
        // TODO: IF LOGGED INV GETS DELETED, CHECK FOR ISSUES (MIGHT HAVE TO MAKE IT SO THE PRODUCT GETS ALL LOCATIONS CREATED AND STORED EVEN IF THERE IS NO PRODUCTS THERE)
        foreach($this->getInventoryLogs() as $item){
            $pi=$item->getLoggedInv();
            $pi->setQuantity($pi->getAmount()+$item->getQuantity());
        }
        $entityManager->flush();
        return $this;
    }
    public function finishOrder(EntityManagerInterface $entityManager): self {
        if($this->paymentType=="cash"){
//            $this->setStripeSession(null);
        }
        if($this->getStripeSession()!=null){
            $session = Session::retrieve($this->getStripeSession())->customer_details;
            $time=new \DateTimeImmutable();
            $this->setUpdatedAt($time);
            $country=$entityManager->getRepository(Countries::class)->findOneBy(['iso2'=>$session['address']['country']]);
            $city=$entityManager->getRepository(Cities::class)->findOneBy(['name'=>$session['address']['city'],'country_code'=>$session['address']['country']]);
            $this->setCountry($country);
            $this->setCity($city);
            $this->setAddressLine1($session['address']['line1']);
            $this->setAddressLine2($session['address']['line2']);
            $this->setPostalCode($session['address']['postal_code']);
            $this->setState($session['address']['state']);
            $this->setCustomerName($session['name']);
//            dd($session['customer_details']);
//            $this->setPhone($session['customer_details']['phone']);
        }
        foreach($this->getInventoryLogs() as $item){
            $this->removeInventoryLog($item);
        }
        $this->setStatus(true);
        $entityManager->flush();
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
            $inventoryLog->setLoggedOrder($this);
        }

        return $this;
    }

    public function removeInventoryLog(InventoryLog $inventoryLog): self
    {
        if ($this->inventoryLogs->removeElement($inventoryLog)) {
            // set the owning side to null (unless already changed)
            if ($inventoryLog->getLoggedOrder() === $this) {
                $inventoryLog->setLoggedOrder(null);
            }
        }

        return $this;
    }

    public function getCity(): ?Cities
    {
        return $this->city;
    }

    public function setCity(?Cities $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?Countries
    {
        return $this->country;
    }

    public function setCountry(?Countries $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): self
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): self
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getSessionExpiration(): ?\DateTimeInterface
    {
        return $this->session_expiration;
    }

    public function setSessionExpiration(\DateTimeInterface $session_expiration): self
    {
        $this->session_expiration = $session_expiration;

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

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function setDiscount(?Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }


}
