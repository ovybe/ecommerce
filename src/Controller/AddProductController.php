<?php

namespace App\Controller;

use App\Entity\Locations;
use App\Entity\Product;
use App\Entity\ProductImages;
use App\Entity\ProductInventory;
use App\Form\AddProductType;
use App\Form\CpuType;
use App\Form\EditProductType;
use App\Form\GpuType;
use App\Form\LocationsType;
use App\Form\MemoryType;
use App\Form\MotherboardType;
use App\Form\PCCaseType;
use App\Form\PsuType;
use App\Form\SsdType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\TwigFunction;
use Symfony\Component\Uid\Uuid;

class AddProductController extends AbstractController
{
    #[Route('/add/product', name: 'app_add_product')]
    public function index(Request $request,ManagerRegistry $managerRegistry, EntityManagerInterface $entityManager, SluggerInterface $slugger, ValidatorInterface $validator): Response
    {
        $types_arr=array(
            'Gpu'=>'GPU',
            'Cpu'=>'CPU',
            'Motherboard'=>'Motherboard',
            'Memory' => 'Memory',
            'Ssd' => 'Storage',
            'Psu' => 'PSU',
            'PCCase' => 'PC Case',
            'Cooler' => 'Cooler',
        );
        $session_arr=array();
        $product = new Product();
        $locations = $managerRegistry->getRepository(Locations::class)->findAll();
        $productInvs = array();
        $productImgs = array();
        for ($i = 0; $i < 5; $i++) {
            $prodimg = new File('', false);
            $productImgs[] = $prodimg;
        }
        if ($request->getSession()->has('stored_form_session') && $session_form=$request->getSession()->get('stored_form_session') ) {
            $form=$this->recoverSession($request,$productImgs);
        } else {
            $productInventories=$this->generateProductInvList($locations);
            $form = $this->createForm(AddProductType::class, array('productInventories' => $productInventories, 'productImages' => $productImgs));
        }
        if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    // handle the form of your type
                    $task = $form->getData();
                    $product=$this->buildProduct($task);
                    $errors=$validator->validate($product,null,['need_validation']);
                    //dd();
                    if(count($errors)>0){
                        // add groups to other forms
                        $product_form=$form->get(strtolower($product->getType()));
                        foreach($errors as $error){
                            $tempError = new FormError($error->getMessage());
                            $product_form->get($error->getPropertyPath())->addError($tempError);
                        }
                    }
                    elseif($product) {
                        $entityManager->persist($product);

                        //dd($task['productImages']);
                        foreach ($task['productImages'] as $prodImg) {
                            $this->handleImage($prodImg,$slugger,$product,$entityManager);
                        }
//                        dd($product);

                        foreach ($task['productInventories'] as $pi) {
                            if ($pi->getQuantity() != null) {
                                $pi->setProduct($product);
                                $product->addProductInventory($pi);
                                $entityManager->persist($pi);
                                // STOPPED AT VENTS
                            }
                        }
                        // ADD BROCHURE FUNCTION HERE
                        $fileToUpload = $form->get('product')->get('thumbnail')->getData();
                        $newFilepath = $this->uploadFile($fileToUpload, $slugger);
                        $product->setThumbnail($newFilepath);
                        // add some checks for product fields
                        //dd($product);
                        $entityManager->persist($product);
                        //dd($product);
                        $entityManager->flush();
                    }
                    else{
                        //dd($product);
                        $productTypeError = new FormError("Must select a product type");
                        $form->get('product')->addError($productTypeError);
                        // ERROR BREAKS THE FORM
                    }
                }
                $newSession= $form->getData();
                // LOOK INTO HOW IT GETS THE DATA AS UPLOADEDFILE
                unset($newSession['thumbnail']);
                unset($newSession['productImages']);
            $request->getSession()->set('stored_form_session',$newSession);
        }
        return $this->render('products/addproduct.html.twig', [
            'controller_name' => 'AddProductController',
            'form' => $form,
            'session' => $session_arr,
            'productTypes' => $types_arr,
//            'errors' => $form->getErrors(true),
        ]);
    }
    #[Route('/add/location', name: 'app_add_location')]
    public function locationAdd(Request $request,ManagerRegistry $managerRegistry, EntityManagerInterface $entityManager): Response
    {
        $location=new Locations();
        $form=$this->createForm(LocationsType::class,$location);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // handle the form of your type
                //dd($location);
                $entityManager->persist($location);
                $entityManager->flush();

            }
        }

        return $this->render('products/addlocation.html.twig', [
            'controller_name' => 'Add Location',
            'form' => $form,
        ]);
    }
    #[Route('/edit/product/{product_id}', name: 'app_edit_product')]
    public function editProduct(Request $request,ManagerRegistry $managerRegistry, EntityManagerInterface $entityManager, SluggerInterface $slugger, ValidatorInterface $validator,string $product_id): Response
    {
        $types_arr=array(
            'Gpu'=>'GPU',
            'Cpu'=>'CPU',
            'Motherboard'=>'Motherboard',
            'Memory' => 'Memory',
            'Ssd' => 'Storage',
            'Psu' => 'PSU',
            'PCCase' => 'PC Case',
            'Cooler' => 'Cooler',
        );
        $product=$managerRegistry->getRepository(Product::class)->findOneBy(['uid'=>$product_id]);
        //dd($product);
        if(!$product){
            return new Response('Product not found',404);
        }
        $locations=$managerRegistry->getRepository(Locations::class)->findAll();
        $productInvs=$product->getProductInventories();
        $productImgs=$product->getProductImages();
        //dd(count($productImgs));
        foreach ($productImgs as $pi){
            dd($pi);
            $prodimg= new File($pi->getPath(),false);

        }
        $maxProd=5-$productImgs->count();
        //dd();
        for ($i = 0; $i < $maxProd; $i++) {
            $prodimg = new File('', false);
            $productImgs->add($prodimg);
        }
        //dd($i);
        //dd($productImgs);
        foreach($productInvs as $pi){
            for($j=0;$j<count($locations);$j++){
                if($pi->getLocation()==$locations[$j]) {
                    unset($locations[$j]);
                    break;
                }
            }
        }
        //dd($locations);
        $productInvs=$this->generateProductInvList($locations);
        foreach($productInvs as $pi){
            $product->addProductInventory($pi);
        }
        //dd($productInvs);
        $prodClassName=$this->get_class_name(get_class($product));
        //$product->setProductImages();
        //dd($product)
        $form=$this->createForm(EditProductType::class,[
            'name'=>$product->getName(),
            'description'=>$product->getDescription(),
            'thumbnail'=>new File($product->getThumbnail()? : '' || $product->getThumbnail(),false),
            'price'=>$product->getPrice(),
            'SKU'=>$product->getSKU(),
            'seller'=>$product->getSeller(),
            'status'=>$product->getStatus(),
            'productInventories'=>$product->getProductInventories(),
            'productImages'=>$productImgs,
            'product' => $product,
            strtolower($prodClassName) => $product]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // handle the form of your type
                $task = $form->getData();
                if($task['type']!=$product->getType()){
                    $newProduct=$this->buildProduct($task);
                    $newProduct->setUid($product->getUid());
                    //returns empty product if no fields were completed
                }else
                    $newProduct=$product;
                //dd($newProduct);
                $errors=$validator->validate($newProduct,null,['need_validation']);
                //dd();
                if(count($errors)>0){
                    // add groups to other forms
                    $product_form=$form->get(strtolower($product->getType()));
                    foreach($errors as $error){
                        $tempError = new FormError($error->getMessage());
                        $product_form->get($error->getPropertyPath())->addError($tempError);
                    }
                }
                elseif($newProduct) {
                    //dd($newProduct,$product);
                    $entityManager->persist($newProduct);

                    //dd($task['productImages']);
                    $newProdIm=$newProduct->getProductImages()->clear();
                    foreach ($task['productImages'] as $prodImg) {
                        if(!$newProduct->getProductImages()->contains($prodImg) && $prodImg instanceof UploadedFile){
                            $this->handleImage($prodImg,$slugger,$newProduct,$entityManager);
                        }
                        else{
                            // STOPPED AT PRODUCT IMAGES GOTTA FIGURE OUT HOW TO HANDLE ALREADY UPLOADED IMAGES
                            //$product->addProductImage($prodImg);
                        }
                    }
//                        dd($product);
                    $newCol=$newProduct->getProductInventories();
                    $newCol->clear();
                    foreach ($task['productInventories'] as $pi) {
                        if ($pi->getQuantity() != null) {
                            $pi->setProduct($newProduct);
                            $newCol->add($pi);
                            $entityManager->persist($pi);
                            // STOPPED AT VENTS
                        }
                    }
                    $product->setProductInventories($newCol);
                    // ADD BROCHURE FUNCTION HERE
                    if($form->get('product')->get('thumbnail')!=$newProduct->getThumbnail())
                        $fileToUpload = $form->get('product')->get('thumbnail')->getData();
                        $newFilepath = $this->uploadFile($fileToUpload, $slugger);
                        $product->setThumbnail($newFilepath);
                    // add some checks for product fields
                    //dd($newProduct,$product);
                    if($newProduct!=$product){
                    $entityManager->persist($newProduct);
                    foreach($product->getProductInventories() as $pi)
                        $entityManager->remove($pi); // CHECK RESTRICTIONS
                    foreach($product->getProductImages() as $pim){
                        if($pim){
                            unlink($pim->getPath()); // NOT SURE IF CORRECT
                            $entityManager->remove($pim);
                        }
                    }
                    $entityManager->remove($product);
                    }
                    //dd($product);
                    $entityManager->flush();
                }
                else{
                    //dd($product);
                    $productTypeError = new FormError("Must select a product type");
                    $form->get('product')->addError($productTypeError);
                    // ERROR BREAKS THE FORM
                }
            }
            $newSession= $form->getData();
            // LOOK INTO HOW IT GETS THE DATA AS UPLOADEDFILE
            unset($newSession['thumbnail']);
            unset($newSession['productImages']);
            $request->getSession()->set('stored_form_session',$newSession);
        }

        return $this->render('products/editproduct.html.twig', [
            'controller_name' => 'Edit Product',
            'form' => $form,
            'locations' => $locations,
//            'session' => $session_arr,
            'productTypes' => $types_arr,
        ]);
    }
    #[Route('/insert/product', name: 'app_insert_product')]
    public function handleForm(Request $request): Response
    {
        $types = [
            'Gpu' => GpuType::class,
            'Cpu' => CpuType::class,
            'Motherboard' => MotherboardType::class,
            'Ssd' => SsdType::class,
            'Hdd' => SsdType::class,
            'Psu' => PsuType::class,
            'PCCase' => PCCaseType::class,
            'Memory' => MemoryType::class,
        ];
        // create the forms based on the types indicated in the types array
        $forms = [];
        foreach ($types as $type) {
            $forms[] = $this->createForm($type);
        }

        if ($request->isMethod('POST')) {
            foreach ($forms as $form) {
                $form->handleRequest($request);

                if (!$form->isSubmitted()) continue; // no need to validate a form that isn't submitted

                if ($form->isValid()) {
                    // handle the form of your type

                    break; // stop processing as we found the form we have to deal with
                }
            }
        }

        $views = [];
        foreach ($forms as $form) {
            $views[] = $form->createView();
        }

        return $this->render('products/insertproduct.html.twig', [
            'controller_name' => 'AddProductController',
            'form' => $views,
            'types' => $types,
        ]);
    }
    private function buildProduct($task): ?Product{
        $temp=strtolower($task['type']);
        $help="App\\Entity\\".$task['type'];
        if(!$temp || !$task[$temp]){
            $emptyProduct=new $help();
            $emptyProduct->setType($task['type']);
            return $emptyProduct;
        }

        $product=$task[$temp];
        $product->setName($task['name']);
        $product->setDescription($task['description']);
        $product->setSKU($task['SKU']);
        $product->setSeller($task['seller']);
        $product->setPrice($task['price']);
        $product->setStatus($task['status']);
        //dd($product);


//        dd($product)
        $uuid = Uuid::v4();
        $product->setUid($uuid);
        $product->setType($temp);
        $product->setCreatedAt(DateTimeImmutable::createFromMutable(new DateTime()));

        return $product;

    }
    private function handleImage(?UploadedFile $prodImg,SluggerInterface $slugger, Product $product, EntityManagerInterface $entityManager){
        if ($prodImg != null) {
            $newFilepath = $this->uploadFile($prodImg, $slugger, 'showcases_directory');
            $tempProdImg = new ProductImages();
            $tempProdImg->setPath($newFilepath)->setProduct($product);
            $product->addProductImage($tempProdImg);
            $entityManager->persist($tempProdImg);
        }
    }
    private function recoverSession(Request $request,?array $productImgs): FormInterface{
        $session_form=$request->getSession()->get('stored_form_session');
        if(array_key_exists('product',$session_form)){
            $form = $this->createForm(AddProductType::class, array('product'=>$session_form['product'],'productInventories' => $session_form['productInventories'],'productImages' => $productImgs));
        }
        else{
            $form = $this->createForm(AddProductType::class, array('productInventories' => $session_form['productInventories'],'productImages' => $productImgs));
        }
        //dd($session_form);
        if(array_key_exists('types',$session_form)){
            $session_arr['type']=$session_form['types'];
        }
        return $form;
    }
    private function uploadFile(?UploadedFile $brochureFile, SluggerInterface $slugger, string $directory='thumbnails_directory'){

        //$brochureFile = $form->get('product')->get('thumbnail')->getData();

        // this condition is needed because the 'brochure' field is not required
        // so the IMG file must be processed only when a file is uploaded
        if ($brochureFile) {
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $brochureFile->move(
                    $this->getParameter($directory),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            return $newFilename;
        }
        else
            return null;
    }

    private function generateProductInvList(array $locations): array
    {
        $productInventories=array();
        foreach ($locations as $location) {
            $pi = new ProductInventory();
            $pi->setLocation($location);
            $pi->setModifiedAt(DateTimeImmutable::createFromMutable(new DateTime()));
            $pi->setCreatedAt(DateTimeImmutable::createFromMutable(new DateTime()));
            $productInventories[] = $pi;
        }
        return $productInventories;
    }
    function get_class_name($classname)
    {
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    }
}
