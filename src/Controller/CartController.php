<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Discount;
use App\Entity\Order;
use App\Entity\PCBuilderTemplate;
use App\Entity\Product;
use App\Form\DiscountCodeType;
use App\Form\OrderPaymentType;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
//    #[Route('/cart', name: 'app_cart')]
//    public function index(CartManager $cartManager,EntityManagerInterface $entityManager): Response
//    {
//        // STOPPED AT CART, WORK ON SESSION EXPIRATION AND RUNNING SERVICE IN BASE.HTML
//        $cart = $cartManager->getCurrentCart();
//
//        $cartManager->save($cart);
//
//        return $this->render('products/cart.html.twig', [
//            'controller_name' => 'Cart',
//            'cart' => $cart,
//        ]);
//    }
    #[Route('/order', name: 'app_cart')]
    public function order_form(Request $request,CartManager $cartManager, EntityManagerInterface $entityManager): Response
    {
        // ADD PAYMENT DETAILS, DISCOUNTS, MAKE FORM
        $cart = $cartManager->getCurrentCart();
        $discountForm=$this->createForm(DiscountCodeType::class);
        if ($request->isMethod('POST')) {
            $discountForm->handleRequest($request);

            if ($discountForm->isSubmitted() && $discountForm->isValid()) {
                $discountCode=$discountForm->getData();

                $discount=$entityManager->getRepository(Discount::class)->findOneBy(['code'=>$discountCode]);
                $now=new \DateTimeImmutable();
                if($discount!=null && $discount->getExpiration()<$now){
                    $error=new FormError("Discount coupon has expired.");
                    $discountForm->addError($error);

                    $orderForm = $this->createForm(OrderPaymentType::class,$cart);

                    return $this->render('products/cart.html.twig', [
                        'controller_name' => 'Cart',
                        'cart' => $cart,
                        'orderForm' => $orderForm,
                        'discountForm' => $discountForm,
                    ]);

                }
                // FOR NOW, IF DISCOUNT IS NULL IT JUST SETS THE DISCOUNT USED AS NULL ALSO
                $cart->setDiscount($discount);
                $cartManager->save($cart);

            }
        }


        $orderForm = $this->createForm(OrderPaymentType::class,$cart);

        return $this->render('products/cart.html.twig', [
            'controller_name' => 'Cart',
            'cart' => $cart,
            'orderForm' => $orderForm,
            'discountForm' => $discountForm,
        ]);
    }
    #[Route('/user/changeOrderType',name: 'app_change_order_type')]
    public function changeOrderType(CartManager $cartManager, Request $request){
        $cart = $cartManager->getCurrentCart();
        $orderForm = $this->createForm(OrderPaymentType::class);
        if ($request->isMethod('POST')) {
            $orderForm->handleRequest($request);

            if ($orderForm->isSubmitted() && $orderForm->isValid()) {
                $order=$orderForm->getData();
                $paymentType=$order->getPaymentType();
                $type=$order->getType();
                $cart->setPaymentType($paymentType);
                $cart->setType($type);
                $cartManager->save($cart);
                return new JsonResponse();
            }
        }
    }
//    #[Route('/add_discount/{code}', name: 'app_add_discount')]
//    public function add_discount(CartManager $cartManager,EntityManagerInterface $entityManager,string $code): JsonResponse
//    {
//        // ADD PAYMENT DETAILS, DISCOUNTS, MAKE FORM
//        $cart=$cartManager->getCurrentCart();
//        $discount=$entityManager->getRepository(Discount::class)->findOneBy(['code'=>$code]);
//        if($discount!=null){
//            $cart->setDiscount($discount);
//        }
//        //dd($itemProduct,$itemProduct->getTotalInventory());
//        $cartManager->save($cart);
//
//        return new JsonResponse($item->getQuantity());
//    }
    #[Route('/add_quantity/{cartItem}', name: 'app_add_quantity')]
    public function add_quantity(CartManager $cartManager,int $cartItem): JsonResponse
    {

        $cart = $cartManager->getCurrentCart();
        $item = $cart->getItems()->get($cartItem);
        $itemProduct = $item->getProduct();
        $newQuantity=$item->getQuantity()+1;

        if($newQuantity<=$itemProduct->getTotalInventory())
            $item->setQuantity($newQuantity);
        $cartManager->save($cart);

        return new JsonResponse($item->getQuantity());
    }
    #[Route('/sub_quantity/{cartItem}', name: 'app_sub_quantity')]
    public function sub_quantity(CartManager $cartManager,int $cartItem): JsonResponse
    {
        $cart = $cartManager->getCurrentCart();
        $item = $cart->getItems()->get($cartItem);
        $newQuantity=$item->getQuantity()-1;

        if($newQuantity>0)
            $item->setQuantity($newQuantity);
        $cartManager->save($cart);

        return new JsonResponse($item->getQuantity());
    }
//    #[Route('/set_quantity/{cartItem}', name: 'app_set_quantity')]
//    public function set_quantity(CartManager $cartManager,int $cartItem): JsonResponse
//    {
//        // ADD PAYMENT DETAILS, DISCOUNTS, MAKE FORM
//        $cart = $cartManager->getCurrentCart();
//        $item = $cart->getItems()->get($cartItem);
//        $itemProduct = $item->getProduct();
//        $newQuantity=$item->getQuantity()-1;
//        //dd($itemProduct,$itemProduct->getTotalInventory());
//        if($newQuantity>0 && $newQuantity<$itemProduct->getTotalInventory())
//            $item->setQuantity($newQuantity);
//        $cartManager->save($cart);
//
//        return new JsonResponse($item->getQuantity());
//    }

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

        $cart->addItem($item);

        $cartManager->save($cart);

        $html = $this->renderView('element_templates/cart.html.twig',['cart'=>$cart]);


        return $this->json($html);
    }
    #[Route('/remove_from_cart/{id}', name: 'app_remove_from_cart')]
    public function remove_from_cart(CartManager $cartManager,EntityManagerInterface $entityManager,int $id): Response
    {
        // STOPPED AT CART, WORK ON SESSION EXPIRATION
        $cart=$cartManager->getCurrentCart();
        $items=$cart->getItems();
        foreach($items as $ci){
            if($ci->getId()==$id){
                $item=$ci;
                $cart->removeItem($item);
                $entityManager->remove($item);
                $entityManager->flush();
                break;
            }
        }


        $html_cart = $this->renderView('element_templates/cart.html.twig',['cart'=>$cart]);
        $html_order = $this->renderView('element_templates/order_items.html.twig',['cart'=>$cart]);

        $json_arr=['cart'=>$html_cart,'order'=>$html_order];


        return $this->json($json_arr);
    }

    #[Route('/remove_all_from_cart/', name: 'app_remove_all_from_cart')]
    public function remove_all_from_cart(CartManager $cartManager,EntityManagerInterface $entityManager): Response
    {

        $cart=$cartManager->getCurrentCart();
        $items=$cart->getItems();
        foreach($items as $ci){
            $cart->removeItem($ci);
            $entityManager->remove($ci);
        }

        $entityManager->flush();


        $html_cart = $this->renderView('element_templates/cart.html.twig',['cart'=>$cart]);
        $html_order = $this->renderView('element_templates/order_items.html.twig',['cart'=>$cart]);

        $json_arr=['cart'=>$html_cart,'order'=>$html_order];


        return $this->json($json_arr);
    }

    #[Route('/add_all_to_cart/{uid}', name: 'app_add_all_to_cart')]
    public function add_all_to_cart(CartManager $cartManager,EntityManagerInterface $entityManager,string $uid): Response
    {
        // STOPPED AT CART, WORK ON SESSION EXPIRATION
        $cart=$cartManager->getCurrentCart();
        $template=$entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(['uid'=>$uid]);
        foreach($template->getCartItems() as $templateCI){
            $templateProduct=$templateCI->getProduct();
            $notFound=true;
            foreach($cart->getItems() as $cartCI){
                if($cartCI->getProduct()==$templateProduct){
                    $cartCI->setQuantity($cartCI->getQuantity()+1);
                    $notFound=false;
                }
            }
            if($notFound) {
                $item = new CartItem();
                $item->setProduct($templateProduct);
                $item->setQuantity($templateCI->getQuantity());
                $item->setAssocOrder($cart);
                $cart->addItem($item);
            }
        }

        $cartManager->save($cart);

        $html = $this->renderView('element_templates/cart.html.twig',['cart'=>$cart]);

        return $this->json($html);
    }
}
