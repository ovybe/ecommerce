<?php
namespace App\Twig;

use App\Manager\CartManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    private CartManager $cartManager;
    public function __construct(CartManager $cartManager){
        $this->cartManager=$cartManager;
    }
    public function getFunctions()
    {
        return [
            new TwigFunction('cart', [$this, 'getCart']),
        ];
    }

    public function getCart()
    {
        return $this->cartManager->getCurrentCart();
    }
}