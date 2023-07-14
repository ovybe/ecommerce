<?php
namespace App\Manager;

use App\Entity\Order;
use App\Factory\OrderFactory;
use App\Service\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
* Class CartManager
* @package App\Manager
*/
class CartManager
{
    /**
    * @var CartSessionStorage
    */
    private $cartSessionStorage;

    /**
    * @var OrderFactory
    */
    private $cartFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Security
     */
    private $security;

    /**
    * CartManager constructor.
    *
    * @param CartSessionStorage $cartStorage
    * @param OrderFactory $orderFactory
    * @param EntityManagerInterface $entityManager
    * @param Security $security
    */
    public function __construct(
        CartSessionStorage $cartStorage,
        OrderFactory $orderFactory,
        EntityManagerInterface $entityManager,
        Security $security,
    ) {
        $this->cartSessionStorage = $cartStorage;
        $this->cartFactory = $orderFactory;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
    * Gets the current cart.
    *
    * @return Order
    */
    public function getCurrentCart(): Order
    {
        $cart = $this->cartSessionStorage->getCart();

        if (!$cart) {
            $cart = $this->cartFactory->create();
        }
//        if ($cart->getUser()==null) {
//            $user = $this->security->getUser();
//            if ($user != null) {
//                $cart->setUser($user);
//            }
//        }

        return $cart;
    }
    /**
     * Persists the cart in database and session.
     *
     * @param Order $cart
     */
    public function save(Order $cart): void
    {
        // Persist in database
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
        // Persist in session
        $this->cartSessionStorage->setCart($cart);
    }
}