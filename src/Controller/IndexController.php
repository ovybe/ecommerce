<?php

namespace App\Controller;

use App\Entity\Cities;
use App\Entity\Contact;
use App\Entity\Cooler;
use App\Entity\Countries;
use App\Entity\Cpu;
use App\Entity\Gpu;
use App\Entity\Locations;
use App\Entity\Memory;
use App\Entity\Motherboard;
use App\Entity\PCCase;
use App\Entity\Product;
use App\Entity\Psu;
use App\Entity\Ssd;
use App\Entity\States;
use App\Entity\User;
use App\Form\ContactType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class IndexController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/index', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
       $products=$this->entityManager->getRepository(Product::class)->getNByType(12);
       $gpus=array();$cpus=array();$mems=array();$mbs=array();$cases=array();$psus=array();$ssds=array();$hdds=array();$coolers=array();
        foreach($products as $p)
        {
            switch($p['type']){
                case 'gpu':
                    $gpus[]=$p;
                    break;
                case 'cpu':
                    $cpus[]=$p;
                    break;
                case 'motherboard':
                    $mbs[]=$p;
                    break;
                case 'memory':
                    $mems[]=$p;
                    break;
                case 'cooler':
                    $coolers[]=$p;
                    break;
                case 'ssd':
                    if($p['drivetype']=="SSD") {
                        $ssds[] = $p;
                        break;
                        }
                case 'hdd':
                    $hdds[]=$p;
                    break;
                case 'pccase':
                    $cases[]=$p;
                    break;
                case 'psu':
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
    public function product_view(ManagerRegistry $doctrine, string $id): Response
    {
        $product=$this->entityManager->getRepository(Product::class)->findOneBy(array('uid'=>$id));

        if($product)
            return $this->render('products/productpage.html.twig', [
                'controller_name' => 'Product Page',
                'product' => $product
            ]);
        else
            return new Response("Product not found", 404);
    }
    #[Route('/admin/', name: 'app_admin')]
    public function admin_menu(ManagerRegistry $doctrine): Response
    {
        $products=$this->entityManager->getRepository(Product::class)->findAll();
        $locations=$this->entityManager->getRepository(Locations::class)->findAll();
        $users=$this->entityManager->getRepository(User::class)->findAll();

        return $this->render('index/admin.html.twig', [
            'controller_name' => 'Admin Page',
            'products' => $products,
            'locations'=> $locations,
            'users'=> $users,
        ]);
    }
    #[Route('/user/settings', name: 'app_user_settings')]
    public function user_settings(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form=$this->createForm(UserType::class,$user);
        $contacts=$user->getContacts()->toArray();
        $contact= new Contact();
        $contact_form=$this->createForm(ContactType::class,$contact);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                // handle the form of your type
                //dd($location);
                //$entityManager->persist($user);
                $this->entityManager->flush();

            }
        }

        return $this->render('registration/user_settings.html.twig', [
            'controller_name' => 'User Settings',
            'form' => $form,
            'contact_form' => $contact_form,
            'contacts' => $contacts,
        ]);
    }
    #[Route('/user/add_contact', name: 'app_add_contact')]
    public function add_contact(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();

        $contact= new Contact();
        $contact->setOwner($user);
        $form=$this->createForm(ContactType::class,$contact);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // handle the form of your type
                //dd($location);
                //$entityManager->persist($user);
                $this->entityManager->persist($contact);
                $this->entityManager->flush();

                $html = $this->renderView('element_templates/contact.html.twig',['contact'=>$contact]);

                return $this->json($html);
            }
        }

        return new Response("Invalid form",400);
    }
    #[Route('/user/edit_contact/{id}', name: 'app_edit_contact')]
    public function edit_contact(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $user = $this->getUser();

        $contacts= $user->getContacts()->toArray();
        $contact=null;
        foreach($contacts as $c){
            if($c->getId()==$id){
                $contact=$c;
                break;
            }
        }
        if(!$contact){
            return new Response("Contact not found",404);
        }
        $form=$this->createForm(ContactType::class,$contact);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // handle the form of your type
                //dd($location);
                //$entityManager->persist($user);
                $this->entityManager->flush();
            }
                return $this->redirectToRoute('app_user_settings',[],302);
        }

        return $this->render('forms/contact.html.twig', [
            'controller_name' => 'Edit Contact',
            'form' => $form,
        ]);
    }
    #[Route('/user/delete/contact/{id}', name: 'app_delete_contact')]
    public function delete_contact(Request $request, EntityManagerInterface $entityManager, int $id){
        if ($request->isMethod('DELETE')) {
            $contact=$entityManager->getRepository(Contact::class)->findOneBy(['id'=>$id]);
            if($contact){
                $entityManager->remove($contact);
                $entityManager->flush();
                return $this->json(['id'=>$id]);
            }
            else return new Response("Contact not found", 404);
        }
        else return new Response("Request type not correct", 400);

    }

}
