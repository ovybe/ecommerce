<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddProductController extends AbstractController
{
    #[Route('/add/product', name: 'app_add_product')]
    public function index(): Response
    {
        return $this->render('products/addproduct.html.twig', [
            'controller_name' => 'AddProductController',
        ]);
    }
}
