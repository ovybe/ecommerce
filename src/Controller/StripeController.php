<?php

namespace App\Controller;

use App\Entity\Cities;
use App\Form\OrderType;
use App\Manager\CartManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\BSON\Timestamp;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {

        return $this->render('index/stripe.html.twig', [
            'controller_name' => 'StripeController',
            'stripe_key' => $_ENV["STRIPE_KEY"],
        ]);
    }
    #[Route('/stripe/cancel', name: 'app_stripe_cancel')]
    public function cancel(CartManager $cartManager): Response
    {
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        $cart=$cartManager->getCurrentCart();
        $stripeSession=$cart->getStripeSession();
        $oldSession=$cart->getStripeSession() ? Session::retrieve($cart->getStripeSession()) : null;
        $oldSession->expire();
        $cart->setSessionExpiration(new \DateTimeImmutable);
        $cartManager->save($cart);
        return $this->redirectToRoute('app_cart');
    }
    #[Route('/stripe/checkout', name: 'app_stripe_checkout', methods: ['POST'])]
    public function checkout(Request $request, CartManager $cartManager, EntityManagerInterface $entityManager)
    {
        $user=$this->getUser();
        $cart=$cartManager->getCurrentCart();
        $items=$cart->getItems();
        $discount=$cart->getTotal()-$cart->getTotalWithDiscount();
        if($items->isEmpty()){
            return $this->redirectToRoute('app_index');
        }
        //TODO: ADD PAYMENT ON DELIVERY OR RESERVATIONS
        if($cart->getPaymentType()=="online"){
            $line_items_arr=array();
            foreach($items as $item){
                $line_items_arr[]=
                    [
                        'price_data' => [
                            'currency' => 'ron',
                            'product_data' => [
                                'name' => $item->getProduct()->getName(),
    //                        'description' => $item->getProduct()->getShortDesc(),
                            ],
                            'unit_amount_decimal' => $item->getTotal()*100,
                        ],
                        'quantity' => $item->getQuantity(),
                    ];
            }
//            $line_items_arr[]=
//                [
//                    'price_data' => [
//                        'currency' => 'ron',
//                        'product_data' => [
//                            'name' => "Discount '".$cart->getDiscount()->getCode()."'",
//                            //                        'description' => $item->getProduct()->getShortDesc(),
//                        ],
//                        'unit_amount_decimal' => $discount,
//                    ],
//                    'quantity' => 1,
//                ];
            Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
            $oldSession=$cart->getStripeSession() ? Session::retrieve($cart->getStripeSession()) : null;
            $checkout_session=null;
            if($oldSession!=null){
                if($oldSession->status=="complete" || $oldSession->payment_status=="paid"){
                    return $this->redirect($oldSession->success_url,303);
                }
                else if(time()<$oldSession->expires_at){
                    $checkout_session=$oldSession;
                }
            }

            if($checkout_session==null || $checkout_session->status=="expired"){
                $checkout_session = Session::create([
                    'line_items' => $line_items_arr,
                    'mode' => 'payment',
                    'success_url' => 'http://localhost:8000/stripe/success',
                    'cancel_url' => 'http://localhost:8000/stripe/cancel',
                    'customer_email' => $user->getEmail(),
                    'shipping_address_collection' => [
                        'allowed_countries' => ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'],
                    ],
                    'phone_number_collection' => [
                        'enabled' => true,
                    ],
                    'invoice_creation' => [
                        'enabled' => true,
                    ],
//                    'amount_subtotal'=> $cart->getTotal(),
//                    'amount_total' => $cart->getTotalWithDiscount(),
                ]);
                $cart->setStripeSession($checkout_session->id);
                $expiration_date=new \DateTimeImmutable();
                $cart->setSessionExpiration($expiration_date->setTimestamp($checkout_session->expires_at));
                $cart->removeInventory($entityManager);
            }
            $cartManager->save($cart);
            return $this->redirect($checkout_session->url,303);
            }elseif($cart->getPaymentType()=="cash"){
                $oneWeekLater = strtotime('+1 Week',time());
                $cart->setSessionExpiration(DateTimeImmutable::createFromFormat('U', $oneWeekLater));
                $cart->removeInventory($entityManager);
                $cartManager->save($cart);
                return $this->redirectToRoute('app_cash_checkout');
        }else{
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/stripe/cash_checkout', name: 'app_cash_checkout')]
    public function cashCheckout(Request $request, CartManager $cartManager,EntityManagerInterface $entityManager){
        $cart=$cartManager->getCurrentCart();
        $orderForm=$this->createForm(OrderType::class);
        if ($request->isMethod('POST')) {
            $orderForm->handleRequest($request);
            if($orderForm->isSubmitted() && $orderForm->isValid()){
                if($cart->getStripeSession()){
                    $cart->setStripeSession(null);
                }
                if(!$cart->getUser()){
                    $cart->setUser($this->getUser());
                }
                $orderData=$orderForm->getData();
                $country=$orderData['country'];
                $cart->setCountry($country);
                $city=$entityManager->getRepository(Cities::class)->findOneBy(['country'=>$country->getId(),'name'=>$orderData['city']]);
                $cart->setCity($city);
                $cart->setAddressLine1($orderData['addressLine1']);
                $cart->setAddressLine2($orderData['addressLine2']);
                $cart->setPostalCode($orderData['postalCode']);
                $cart->setPhone($country->getPhoneCode().$orderData['phone']);
                $cart->setCustomerName($orderData['customerName']);

                $entityManager->flush();
                return $this->redirectToRoute('app_stripe_success');
            }
        }
        return $this->render('forms/checkout.html.twig', [
            'controller_name' => 'StripeController',
            'form'=>$orderForm,
        ]);
    }

    #[Route('/stripe/success', name: 'app_stripe_success')]
    public function stripe_success(Request $request, CartManager $cartManager,EntityManagerInterface $entityManager)
    {
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        $order=$cartManager->getCurrentCart();
        $session = $order->getStripeSession() ? Session::retrieve($order->getStripeSession()) : null;

//        dd($session);
        if($session==null){
            if($order->getPaymentType()=="cash"){
                $order->finishOrder($entityManager);
            }else{
                return $this->redirectToRoute('app_cart');
            }
        }else
        if($session->status!="complete"){
            return $this->redirectToRoute('app_cart');
        }else
        if($session->payment_status=="paid" && !$order->isStatus()){
            $order->finishOrder($entityManager);
        }

        return $this->render('index/stripe_success.html.twig', [
            'controller_name' => 'Successful Buy',
            'order' => $order,
            'stripe_key' => $_ENV["STRIPE_KEY"],
        ]);
    }
}
