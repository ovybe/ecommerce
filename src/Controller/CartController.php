<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartManager $cartManager,EntityManagerInterface $entityManager): Response
    {
        // STOPPED AT CART, WORK ON SESSION EXPIRATION AND RUNNING SERVICE IN BASE.HTML
        $cart = $cartManager->getCurrentCart();

        $cartManager->save($cart);

        return $this->render('products/cart.html.twig', [
            'controller_name' => 'Cart',
            'cart' => $cart,
        ]);
    }

    public function refresh_cart(CartManager $cartManager){
        $user=$this->getUser();
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

    #[Route('/add_to_cart/{uid}', name: 'app_add_to_cart')]
    public function add_to_cart(CartManager $cartManager,EntityManagerInterface $entityManager,string $uid): Response
    {
        // STOPPED AT CART, WORK ON SESSION EXPIRATION
        $cart=$cartManager->getCurrentCart();
        $product=$entityManager->getRepository(Product::class)->findOneBy(['uid'=>$uid]);
        $item=new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);
        $item->setAssocOrder($cart);

        //$entityManager->persist($item);
        //$entityManager->flush();
        $cart->addItem($item);

        $cartManager->save($cart);

        //dd($cart);
        $html = $this->renderView('element_templates/cart.html.twig',['cart'=>$cart]);

        //dd($html);

        return $this->json($html);
    }
}
