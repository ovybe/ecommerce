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
    #[Route('/admin/edit/discount/{id}', name: 'app_admin_edit_discount')]
    public function editDiscount(Request $request,EntityManagerInterface $entityManager, int $id): Response
    {
        $discount = $entityManager->getRepository(Discount::class)->findOneBy(['id'=>$id]);

        $timezone=$this->getUser()->getCountry()->getTimezones()[0];

        // CLONING WAS NECESSARY DUE TO TIMEZONE ISSUES
        $discountClone= clone $discount;
        $discountClone->setExpiration(null);

        $form = $this->createForm(DiscountType::class,$discountClone,['timezone'=>$timezone->zoneName]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $datetime=new \DateTimeImmutable();

                $discountClone->getExpiration()->setTimezone(new \DateTimeZone('UTC'));
                $discount->setCode($discountClone->getCode());
                $discount->setPrice($discountClone->getPrice());
                $discount->setPercentage($discountClone->isPercentage());
                $discount->setUses($discountClone->getUses());
                $discount->setExpiration($discountClone->getExpiration());

                $discount->setModifiedAt($datetime);

                $entityManager->flush();

                return $this->redirectToRoute('app_admin');
            }
        }

        return $this->render('forms/discount.html.twig', [
            'controller_name' => 'Edit Discount',
            'form' => $form,
            'exptime' => $discount->getExpiration(),
            'timezone' => $timezone->zoneName,
        ]);
    }
    #[Route('/admin/delete/discount/{id}', name: 'app_admin_delete_discount')]
    public function deleteDiscount(Request $request,EntityManagerInterface $entityManager, int $id): Response
    {
        $discount=$entityManager->getRepository(Discount::class)->findOneBy(['id'=>$id]);
        if($discount==null){
            return new Response("Discount coupon to delete not found",404);
        }
        $entityManager->remove($discount);
        $entityManager->flush();
        return $this->redirectToRoute('app_admin');
    }
}
