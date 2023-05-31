<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Discount;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\DiscountType;
use App\Form\OrderPaymentType;
use App\Manager\CartManager;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver\Middleware\EnableForeignKeys;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class DiscountController extends AbstractController
{
    #[Route('/admin/add/discount', name: 'app_admin_add_discount')]
    public function addDiscount(Request $request,EntityManagerInterface $entityManager): Response
    {
        $discount = new Discount();
        $timezone=$this->getUser()->getCountry()->getTimezones()[0];
        $form = $this->createForm(DiscountType::class,$discount,['timezone'=>$timezone->zoneName]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $datetime=new \DateTimeImmutable();
//                $discount->getExpiration()->setTimezone(new \DateTimeZone('UTC'));
                // TODO: Add validation
                $discount->setCreatedAt($datetime);
                $discount->setModifiedAt($datetime);
                $entityManager->persist($discount);
                $entityManager->flush();

                return $this->redirectToRoute('app_admin');
            }
        }

        return $this->render('forms/discount.html.twig', [
            'controller_name' => 'Add Discount',
            'form' => $form,
        ]);
    }
}
