<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PCBuilderController extends AbstractController
{
    #[Route('/pcbuilder', name: 'app_pcbuilder')]
    public function index(): Response
    {
        return $this->render('products/pcbuilder.html.twig', [
            'controller_name' => 'PCBuilderController',
        ]);
    }
}
