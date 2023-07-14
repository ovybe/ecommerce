<?php

namespace App\Controller;

use App\Entity\Category;
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
use App\Entity\PCBuilderTemplate;
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
use phpDocumentor\Reflection\Types\Boolean;
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
use function PHPUnit\Framework\arrayHasKey;

class IndexController extends AbstractController
{
    private $entityManager;
    private EmailVerifier $emailVerifier;

    public function __construct(EntityManagerInterface $entityManager,EmailVerifier $emailVerifier)
    {
        $this->entityManager = $entityManager;
        $this->emailVerifier = $emailVerifier;
    }

    public function groupFiltersByColumn($filters_ungrouped){
        $filters_grouped=[];
        foreach($filters_ungrouped as $filter){
            $filters_grouped[$filter['option_name']][]=[$filter['option_value'],$filter['option_count']];
        }
        return $filters_grouped;
    }
    public function getFilterArray(){
        $filters_ungrouped=$this->entityManager->getRepository(Product::class)->getFilters();
        return $this->groupFiltersByColumn($filters_ungrouped);

    }
    public function getFilterArrayByCategoryId($category_id){
        $filters_ungrouped=$this->entityManager->getRepository(Product::class)->findFiltersByCategoryId($category_id);
        return $this->groupFiltersByColumn($filters_ungrouped);
    }
    public function getFilterArrayBySearch($value){
        $filters_ungrouped=$this->entityManager->getRepository(Product::class)->FindFiltersByValue($value);
        return $this->groupFiltersByColumn($filters_ungrouped);
    }

    #[Route('/index', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $products=[
            1 =>[],
            2 =>[],
            3 =>[],
            4 =>[],
            5 =>[],
            6 =>[],
            7 =>[],
            8 =>[],
            9 =>[],
        ];
        # TODO: Add filters like Emag for example (you tick a filter's box and it uses js to update the list or make it a form that adds onto the search/index and filters the already existent list like pcgarage)
        $all = $this->entityManager->getRepository(Product::class)->findAmountByStatusAndQuantity(1,12);
        foreach($all as $product){
            $products[$product->getCategory()->getId()][]=$product;
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'Index Page',
            'products'=>$products,
        ]);
    }
    #[Route('/catalog/{value}', name: 'app_catalog')]
    public function catalog(string $value=null): Response
    {
        if($value==null){
            $products=$this->entityManager->getRepository(Product::class)->findAll();
            $filters = $this->getFilterArray();
            $category_id = 0; # Means all categories
        }else{
            $category=$this->entityManager->getRepository(Category::class)->findOneBy(['category_name'=>$value]);
            if(empty($category)){
                return new Response('Category not found!', 404);
            }
            else {
                $category_id = $category->getId();
                $products = $this->entityManager->getRepository(Product::class)->findBy(['category' => $category_id]);
                $filters = $this->getFilterArrayByCategoryId($category_id);
            }
        }

        if(empty($products)){
            return new Response('Products for category '.$value.' not found!',404);
        }


        #dd($filters);

        return $this->render('index/search.html.twig', [
            'controller_name' => 'Search Product',
            'prods' => $products,
            'filters' => $filters,
            'value' => $category_id,
            'function' => 0,
        ]);
//        else
//            return new Response("No products found.", 404);
    }
    #[Route('/search/{value}', name: 'app_search')]
    public function search(string $value=null): Response
    {
        $products=$this->entityManager->getRepository(Product::class)->findAllByValue($value);
        $filters = $this->getFilterArrayBySearch($value);

        # TODO: FIX FILTERS, EDIT, ADMIN MENU
//        if($products)
            return $this->render('index/search.html.twig', [
                'controller_name' => 'Search Product',
                'prods' => $products,
                'filters' => $filters,
                'value' => $value,
                'function' => 1
            ]);
//        else
//            return new Response("No products found.", 404);
    }
    #[Route('/apply_filter/', name:'app_apply_filters')]
    public function apply_filters(Request $request): Response
    {
        $filter_array = $request->get('filter_array');
        $function_used = $request->get('function');
        $search_value = $request->get('value');

        $obj = Product::class;
        if(empty($filter_array)){
            $filter_array=[];
        }
        $products= $this->entityManager->getRepository($obj)->findByFiltersAndFunctionValue($filter_array,$function_used,$search_value);

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
        $templates=$this->entityManager->getRepository(PCBuilderTemplate::class)->findAll();

        return $this->render('index/admin.html.twig', [
            'controller_name' => 'Admin Page',
            'products' => $products,
            'locations'=> $locations,
            'users'=> $users,
            'discounts' => $discounts,
            'templates' => $templates,
        ]);
    }
    #[Route('/user/settings', name: 'app_user_settings')]
    public function user_settings(Request $request, UserPasswordHasherInterface $userPasswordHasher,EmailVerifier $emailVerifier): Response
    {
        $user = $this->getUser();
        $form=$this->createForm(UserType::class,$user);
        $contacts=$user->getContacts()->toArray();
        $templates=$user->getPCBuilderTemplates()->toArray();
        $contact= new Contact();
        $contact_form=$this->createForm(ContactType::class,$contact);
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
            'contacts' => $contacts,
            'templates' => $templates,
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
//    #[Route('/user/add_contact', name: 'app_add_contact')]
//    public function add_contact(ManagerRegistry $doctrine, Request $request): Response
//    {
//        $user = $this->getUser();
//
//        $contact= new Contact();
//        $contact->setOwner($user);
//        $form=$this->createForm(ContactType::class,$contact);
//
//        if ($request->isMethod('POST')) {
//            $form->handleRequest($request);
//            if ($form->isSubmitted() && $form->isValid()) {
//                // handle the form of your type
//                //dd($location);
//                //$entityManager->persist($user);
//                $this->entityManager->persist($contact);
//                $this->entityManager->flush();
//
//                $html = $this->renderView('element_templates/contact.html.twig',['contact'=>$contact]);
//
//                return $this->json($html);
//            }
//        }
//
//        return new Response("Invalid form",400);
//    }
//    #[Route('/user/add_payment', name: 'app_add_payment')]
//    public function add_payment(ManagerRegistry $doctrine, Request $request): Response
//    {
//        $user = $this->getUser();
//
//        $paymentDetail= new PaymentDetail();
//        $paymentDetail->setOwner($user);
//        $paymentDetail->setCreatedAt(new \DateTimeImmutable());
//        $paymentDetail->setModifiedAt(new \DateTimeImmutable());
//        $form=$this->createForm(PaymentDetailType::class,$paymentDetail);
//
//        if ($request->isMethod('POST')) {
//            $form->handleRequest($request);
//            if ($form->isSubmitted() && $form->isValid()) {
//                // handle the form of your type
//                //dd($location);
//                //$entityManager->persist($user);
//                $this->entityManager->persist($paymentDetail);
//                $this->entityManager->flush();
//
//                $html = $this->renderView('element_templates/payment_detail.html.twig',['payment'=>$paymentDetail]);
//
//                return $this->json($html);
//            }
//        }
//
//        return new Response("Invalid form",400);
//    }
//    #[Route('/user/edit_contact/{id}', name: 'app_edit_contact')]
//    public function edit_contact(ManagerRegistry $doctrine, Request $request, int $id): Response
//    {
//        $user = $this->getUser();
//
//        $contacts= $user->getContacts()->toArray();
//        $contact=null;
//        foreach($contacts as $c){
//            if($c->getId()==$id){
//                $contact=$c;
//                break;
//            }
//        }
//        if(!$contact){
//            return new Response("Contact not found",404);
//        }
//        $form=$this->createForm(ContactType::class,$contact);
//
//        if ($request->isMethod('POST')) {
//            $form->handleRequest($request);
//            if ($form->isSubmitted() && $form->isValid()) {
//                // handle the form of your type
//                //$entityManager->persist($user);
//                $this->entityManager->flush();
//            }
//                return $this->redirectToRoute('app_user_settings',[],302);
//        }
//
//        return $this->render('forms/contact.html.twig', [
//            'controller_name' => 'Edit Contact',
//            'form' => $form,
//        ]);
//    }
//    #[Route('/user/edit_payment/{id}', name: 'app_edit_payment')]
//    public function edit_payment(ManagerRegistry $doctrine, Request $request, int $id): Response
//    {
//        $user = $this->getUser();
//
//        $payments= $user->getPaymentDetails()->toArray();
//        $payment=null;
//        foreach($payments as $p){
//            if($p->getId()==$id){
//                $payment=$p;
//                break;
//            }
//        }
//        if(!$payment){
//            return new Response("Payment detail not found",404);
//        }
//        $form=$this->createForm(PaymentDetailType::class,$payment);
//
//        if ($request->isMethod('POST')) {
//            $form->handleRequest($request);
//            if ($form->isSubmitted() && $form->isValid()) {
//                // handle the form of your type
//
//                //$entityManager->persist($user);
//                $payment->setModifiedAt(new \DateTimeImmutable());
//                $this->entityManager->flush();
//            }
//            return $this->redirectToRoute('app_user_settings',[],302);
//        }
//
//        return $this->render('forms/payment.html.twig', [
//            'controller_name' => 'Edit Payment Details',
//            'form' => $form,
//        ]);
//    }
//    #[Route('/user/delete/contact/{id}', name: 'app_delete_contact')]
//    public function delete_contact(Request $request, EntityManagerInterface $entityManager, int $id){
//        if ($request->isMethod('DELETE')) {
//            $contact=$entityManager->getRepository(Contact::class)->findOneBy(['id'=>$id]);
//            if($contact){
//                $entityManager->remove($contact);
//                $entityManager->flush();
//                return $this->json(['id'=>$id]);
//            }
//            else return new Response("Contact not found", 404);
//        }
//        else return new Response("Request type not correct", 400);
//
//    }
    #[Route('/user/delete/template/{id}', name: 'app_user_delete_template')]
    public function user_delete_template(Request $request, EntityManagerInterface $entityManager, string $id){
        if ($request->isMethod('DELETE')) {
            $template=$entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(["owningUser"=>$this->getUser()->getId(),'uid'=>$id]);
            if($template){
                $entityManager->remove($template);
                $entityManager->flush();
                return $this->json(['id'=>$id]);
            }
            else return new Response("Template not found", 404);
        }
        else return new Response("Request type not correct", 400);

    }
    #[Route('/admin/toggle/suspension/{id}', name: 'app_admin_toggle_suspend')]
    public function toggle_suspension(Request $request, EntityManagerInterface $entityManager, int $id){
        $userRepo = User::class;
        $suspendedRole = 'ROLE_SUSPENDED';
        if($this->getUser()->isAdmin()){
            $user=$entityManager->getReference($userRepo,$id);
            $userRoles=$user->getRoles();
            // CHECK FOR SUSPENDED ROLE
            if (($key = array_search($suspendedRole, $userRoles)) !== false) {
                unset($userRoles[$key]);
            }else{
                $userRoles[]=$suspendedRole;
            }
            $user->setRoles($userRoles);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin');
        }else return new Response("User must be an administrator", 403);
    }

    #[Route('/admin/delete/template/{id}', name: 'app_admin_delete_template')]
    public function admin_delete_template(Request $request, EntityManagerInterface $entityManager, string $id){
        if ($this->getUser()->isAdmin()) {
            $template=$entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(['uid'=>$id]);
            if($template){
                $entityManager->remove($template);
                $entityManager->flush();
                return $this->redirectToRoute('app_admin');
            }
            else return new Response("Template not found", 404);
        }
        else return new Response("User is not an admin", 403);

    }

}
