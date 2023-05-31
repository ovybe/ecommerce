<?php

namespace App\Controller;

use App\Entity\Cities;
use App\Entity\Contact;
use App\Entity\Cooler;
use App\Entity\Countries;
use App\Entity\Cpu;
use App\Entity\Discount;
use App\Entity\Gpu;
use App\Entity\Locations;
use App\Entity\Memory;
use App\Entity\Motherboard;
use App\Entity\PaymentDetail;
use App\Entity\PCCase;
use App\Entity\Product;
use App\Entity\Psu;
use App\Entity\Ssd;
use App\Entity\States;
use App\Entity\User;
use App\Form\ContactType;
use App\Form\PaymentDetailType;
use App\Form\UserChangeEmailFormType;
use App\Form\UserChangePasswordFormType;
use App\Form\UserType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class IndexController extends AbstractController
{
    private $entityManager;
    private EmailVerifier $emailVerifier;

    public function __construct(EntityManagerInterface $entityManager,EmailVerifier $emailVerifier)
    {
        $this->entityManager = $entityManager;
        $this->emailVerifier = $emailVerifier;
    }

    public function getFilterArray($repository){
        $classname = ucfirst($repository);
        $obj=str_replace('Product',$classname,Product::class);
        $filters_ungrouped=$this->entityManager->getRepository($obj)->getFilters();
        $filters_grouped=[];
        foreach($filters_ungrouped as $filter){
            $filters_grouped[$filter['filter_column']][]=[$filter['filter_name'],$filter['count_filter']];
        }
        return $filters_grouped;
    }
    public function getFilterArrayBySearch($value){
        $filters_ungrouped=$this->entityManager->getRepository(Product::class)->FindFiltersByValue($value);
        $filters_grouped=[];
        foreach($filters_ungrouped as $filter){
            $filters_grouped[$filter['filter_column']][]=[$filter['filter_name'],$filter['count_filter']];
        }
        return $filters_grouped;
    }

    #[Route('/index', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {

        # TODO: Add filters like Emag for example (you tick a filter's box and it uses js to update the list or make it a form that adds onto the search/index and filters the already existent list like pcgarage)
        $gpus = $this->entityManager->getRepository(Gpu::class)->findAllByStatusAndQuantity(1,12);

        $cpus = $this->entityManager->getRepository(Cpu::class)->findAllByStatusAndQuantity(1,12);
        $mems = $this->entityManager->getRepository(Memory::class)->findAllByStatusAndQuantity(1,12);
        $mbs =  $this->entityManager->getRepository(Motherboard::class)->findAllByStatusAndQuantity(1,12);
        $cases =  $this->entityManager->getRepository(PCCase::class)->findAllByStatusAndQuantity(1,12);
        $psus = $this->entityManager->getRepository(Psu::class)->findAllByStatusAndQuantity(1,12);
        $hdds = $this->entityManager->getRepository(Ssd::class)->findAllByStatusAndQuantitySsd(1,12,'HDD');
        $ssds = $this->entityManager->getRepository(Ssd::class)->findAllByStatusAndQuantitySsd(1,12,'SSD');
        $coolers = $this->entityManager->getRepository(Cooler::class)->findAllByStatusAndQuantity(1,12);

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
    #[Route('/catalog/{value}', name: 'app_catalog')]
    public function catalog(string $value=null): Response
    {
        if($value==null){
            $products=$this->entityManager->getRepository(Product::class)->findAll();
        }
        else
            $products=$this->entityManager->getRepository(Product::class)->findBy(['type'=>strtolower($value)]);
        if(empty($products)){
            return new Response('Product category not found!',404);
        }
        $filters = $this->getFilterArray($value);
        #dd($filters);

        return $this->render('index/search.html.twig', [
            'controller_name' => 'Search Product',
            'prods' => $products,
            'filters' => $filters,
            'category' => $value,
        ]);
//        else
//            return new Response("No products found.", 404);
    }
    #[Route('/search/{value}', name: 'app_search')]
    public function search(string $value=null): Response
    {
        $products=$this->entityManager->getRepository(Product::class)->findAllByValue($value);
        $filters = $this->getFilterArrayBySearch($value);

//        if($products)
            return $this->render('index/search.html.twig', [
                'controller_name' => 'Search Product',
                'prods' => $products,
                'filters' => $filters,
                'category' => '',
            ]);
//        else
//            return new Response("No products found.", 404);
    }
    #[Route('/apply_filter/', name:'app_apply_filters')]
    public function apply_filters(Request $request): Response
    {
        $filter_array = $request->get('filter_array');
        $category = $request->get('category');
        if($category=='') {
            $obj = Product::class;
        }
        else {
            $classname = ucfirst($category);
            $obj = str_replace('Product', $classname, Product::class);
        }
        if($filter_array!=null)
            if($obj==Product::class){
                $products= $this->entityManager->getRepository($obj)->findByFilters($filter_array);
                dd($products);
            }
            else
                $products= $this->entityManager->getRepository($obj)->findBy($filter_array);
        else
            $products= $this->entityManager->getRepository($obj)->findAll();
        $html = $this->renderView('element_templates/products_generate.html.twig',['prods'=>$products]);

        return $this->json($html);
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
        $discounts=$this->entityManager->getRepository(Discount::class)->findAll();

        return $this->render('index/admin.html.twig', [
            'controller_name' => 'Admin Page',
            'products' => $products,
            'locations'=> $locations,
            'users'=> $users,
            'discounts' => $discounts,
        ]);
    }
    #[Route('/user/settings', name: 'app_user_settings')]
    public function user_settings(Request $request, UserPasswordHasherInterface $userPasswordHasher,EmailVerifier $emailVerifier): Response
    {
        $user = $this->getUser();
        $form=$this->createForm(UserType::class,$user);
        $contacts=$user->getContacts()->toArray();
        $payments=$user->getPaymentDetails()->toArray();
        $contact= new Contact();
        $contact_form=$this->createForm(ContactType::class,$contact);
        $payment= new PaymentDetail();
        $payment_form=$this->createForm(PaymentDetailType::class,$payment);
        $password_form=$this->createForm(UserChangePasswordFormType::class);
        $email_form=$this->createForm(UserChangeEmailFormType::class,$user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $password_form->handleRequest($request);
            $email_form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();
            }
            if ($password_form->isSubmitted() && $password_form->isValid()) {
                if($userPasswordHasher->isPasswordValid($user,$password_form->get('oldPassword')->getData()))
                    // CHECK IF THE RESET PASSWORD IS THE SAME AS OLD ONE MAYBE?
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $password_form->get('changePassForm')->get('plainPassword')->getData()
                        )
                    );
                else{
                    $passError = new FormError("Old password doesn't match!");
                    $password_form->get('oldPassword')->addError($passError);
                }
            }

            if ($email_form->isSubmitted() && $email_form->isValid()) {
                    $user->setIsVerified(false);

                    // generate a signed url and email it to the user
                    $this->sendVerificationMail();
                    $this->entityManager->flush();
                // SET USER TO NOT VERIFIED AND SEND CONFIRMATION EMAIL
                // IF USER COMES BACK TO PAGE AND HASN'T VERIFIED HIS EMAIL YET SEND A CONFIRMATION EMAIL
                // USER WILL NOT BE ABLE TO ORDER UNLESS EMAIL IS VERIFIED
                // CHECK IF EMAIL IS THE SAME BEFORE DOING THIS
                // MAKE IT SO FORM HAS "example@examplemail.com (unverified)" IF USER ENTERS SETTINGS WITH UNVERIFIED EMAIL
//                $user->setIsVerified(false);
//                $this->entityManager->flush();
            }
        }

        return $this->render('registration/user_settings.html.twig', [
            'controller_name' => 'User Settings',
            'form' => $form,
            'contact_form' => $contact_form,
            'email_form' => $email_form,
            'password_form' => $password_form,
            'payment_form' => $payment_form,
            'contacts' => $contacts,
            'payments' => $payments,
        ]);
    }
    private function sendVerificationMail(){
        $user = $this->getUser();
        if($user->isVerified()){
            return new Response('Email already verified', 400);
        }
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('ovidiu.butiu01@e-uvt.ro', 'Shoppe Mail Bot'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
    #[Route('/user/resend', name: 'app_user_resend', methods:'post')]
    public function resend_mail(){
        $this->sendVerificationMail();
        return new JsonResponse();
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
    #[Route('/user/add_payment', name: 'app_add_payment')]
    public function add_payment(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();

        $paymentDetail= new PaymentDetail();
        $paymentDetail->setOwner($user);
        $paymentDetail->setCreatedAt(new \DateTimeImmutable());
        $paymentDetail->setModifiedAt(new \DateTimeImmutable());
        $form=$this->createForm(PaymentDetailType::class,$paymentDetail);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // handle the form of your type
                //dd($location);
                //$entityManager->persist($user);
                $this->entityManager->persist($paymentDetail);
                $this->entityManager->flush();

                $html = $this->renderView('element_templates/payment_detail.html.twig',['payment'=>$paymentDetail]);

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
    #[Route('/user/edit_payment/{id}', name: 'app_edit_payment')]
    public function edit_payment(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $user = $this->getUser();

        $payments= $user->getPaymentDetails()->toArray();
        $payment=null;
        foreach($payments as $p){
            if($p->getId()==$id){
                $payment=$p;
                break;
            }
        }
        if(!$payment){
            return new Response("Payment detail not found",404);
        }
        $form=$this->createForm(PaymentDetailType::class,$payment);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // handle the form of your type
                //dd($location);
                //$entityManager->persist($user);
                $payment->setModifiedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
            }
            return $this->redirectToRoute('app_user_settings',[],302);
        }

        return $this->render('forms/payment.html.twig', [
            'controller_name' => 'Edit Payment Details',
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
    #[Route('/user/delete/payment/{id}', name: 'app_delete_payment')]
    public function delete_payment(Request $request, EntityManagerInterface $entityManager, int $id){
        if ($request->isMethod('DELETE')) {
            $payment=$entityManager->getRepository(PaymentDetail::class)->findOneBy(['id'=>$id]);
            if($payment){
                $entityManager->remove($payment);
                $entityManager->flush();
                return $this->json(['id'=>$id]);
            }
            else return new Response("Payment not found", 404);
        }
        else return new Response("Request type not correct", 400);

    }

}
