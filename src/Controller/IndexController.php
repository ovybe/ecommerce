<?php

namespace App\Controller;

use App\Entity\Cooler;
use App\Entity\Countries;
use App\Entity\Cpu;
use App\Entity\Gpu;
use App\Entity\Memory;
use App\Entity\Motherboard;
use App\Entity\PCCase;
use App\Entity\Product;
use App\Entity\Psu;
use App\Entity\Ssd;
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
       $products=$entityManager->getRepository(Product::class)->getNByType(12);
       $gpus=array();$cpus=array();$mems=array();$mbs=array();$cases=array();$psus=array();$ssds=array();$hdds=array();$coolers=array();
        foreach($products as $p)
        {
            switch($p['type']){
                case 'GPU':
                    $gpus[]=$p;
                    break;
                case 'CPU':
                    $cpus[]=$p;
                    break;
                case 'Motherboard':
                    $mbs[]=$p;
                    break;
                case 'Memory':
                    $mems[]=$p;
                    break;
                case 'Cooler':
                    $coolers[]=$p;
                    break;
                case 'SSD':
                    $ssds[]=$p;
                    break;
                case 'HDD':
                    $hdds[]=$p;
                    break;
                case 'Case':
                    $cases[]=$p;
                    break;
                case 'PSU':
                    $psus[]=$p;
                    break;
            }
        }


        return $this->render('index/index.html.twig', [
            'controller_name' => 'Index Page',
            'gpus' => $gpus,
            'cpus' => $cpus,
            'mems' => $mems,
            'mbs' => $mbs,
            'cases' => $cases,
            'psus' => $psus,
            'ssds' => $ssds,
            'hdds' => $hdds,
            'coolers' => $coolers,
        ]);
    }
    #[Route('/product/{id}', name: 'app_product')]
    public function product_view(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        $product=$entityManager->getRepository(Product::class)->findOneBy(array('id'=>$id));

        return $this->render('products/productpage.html.twig', [
            'controller_name' => 'Product Page',
            'product' => $product
        ]);
    }
}
