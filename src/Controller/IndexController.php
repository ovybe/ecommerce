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

        $gpus=$entityManager->getRepository(Gpu::class)->findBy(array(),array("name" => 'ASC'),12);
        $cpus=$entityManager->getRepository(Cpu::class)->findBy(array(),array("name" => 'ASC'),12);
        $mems=$entityManager->getRepository(Memory::class)->findBy(array(),array("name" => 'ASC'),12);
        $mbs=$entityManager->getRepository(Motherboard::class)->findBy(array(),array("name" => 'ASC'),12);
        $cases=$entityManager->getRepository(PCCase::class)->findBy(array(),array("name" => 'ASC'),12);
        $psus=$entityManager->getRepository(Psu::class)->findBy(array(),array("name" => 'ASC'),12);
        $ssds=$entityManager->getRepository(Ssd::class)->findBy(array(),array("name" => 'ASC'),12);
        $hdds=$entityManager->getRepository(Ssd::class)->findBy(array(),array("name" => 'ASC'),12);
        $coolers=$entityManager->getRepository(Cooler::class)->findBy(array(),array("name" => 'ASC'),12);

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
}
