<?php

namespace App\Controller;

use App\Entity\Countries;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{

    #[Route('/index', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
//
//        $product = new Countries();
//        $product->setName('China');
//        $product->setCode(86);
//        $product->setContinent('AS');
//
//        // tell Doctrine you want to (eventually) save the Product (no queries yet)
//        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
//        $entityManager->flush();
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
