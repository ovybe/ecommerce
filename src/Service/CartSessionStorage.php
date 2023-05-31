<?php
namespace App\Service;

use App\Entity\Order;
use App\Manager\CartManager;
use App\Repository\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
* Class CartSessionStorage
* @package App\Storage
*/
class CartSessionStorage
{
    /**
    * @var RequestStack
    */
    private $requestStack;

    /**
    * The cart repository.
    *
    * @var OrderRepository
    */
    private $cartRepository;

    /**
     * The Security Service.
     *
     * @var Security
     */
    private $security;

    /**
    * @var string
    */
    const CART_KEY_NAME = 'cart_id';

    /**
    * CartSessionStorage constructor.
    *
    * @param RequestStack $requestStack
    * @param OrderRepository $cartRepository
    */
    public function __construct(RequestStack $requestStack, OrderRepository $cartRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->cartRepository = $cartRepository;
        $this->security = $security;
    }

    /**
    * Gets the cart in session.
    *
    * @return Order|null
    */
    public function getCart(): ?Order
    {
    return $this->cartRepository->findOneBy([
        'id' => $this->getCartId(),
        'status' => Order::STATUS_CART
    ]);
    }
    /**
     * Gets the user's cart in session.
     *
     * @return Order|null
     */
    public function getCartByUser(): ?Order
    {
        return $this->cartRepository->findOneBy([
            'id' => $this->getCartId(),
            'status' => Order::STATUS_CART,
            'user_id' => $this->security->getUser()->getId(),
        ]);
    }

    /**
    * Sets the cart in session.
    *
    * @param Order $cart
    */
    public function setCart(Order $cart): void
    {
        $this->requestStack->getSession()->set(self::CART_KEY_NAME, $cart->getId());
    }

    /**
    * Returns the cart id.
    *
    * @return int|null
    */
    private function getCartId(): ?int
    {
        return $this->requestStack->getSession()->get(self::CART_KEY_NAME);
    }

    public function refresh_cart(CartManager $cartManager){
        $user=$this->security->getUser();
        $cart = $cartManager->getCurrentCart();
        if($user){
            $userCart=$user->getOrderCart();
            if(!$userCart || $userCart->isStatus()){
                $cart->setUser($user);
                $user->setOrderCart($cart);
            }
            else{
                $cart=$userCart;
            }
            $cartManager->save($cart);
        }
    }
}
