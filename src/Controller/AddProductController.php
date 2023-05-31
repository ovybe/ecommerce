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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
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
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;
    private ValidatorInterface $validator;
    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
        $this->validator = $validator;
    }
    #[Route('/admin/add/product', name: 'app_add_product')]
    public function index(Request $request,ManagerRegistry $managerRegistry): Response
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
//        if ($request->getSession()->has('stored_form_session') && $session_form=$request->getSession()->get('stored_form_session') ) {
//            $form=$this->recoverSession($request,$productImgs); // THIS IS BUGGY AS HELL, GENERATE NEW PRODUCT INV LIST
//        } else {
            $productInventories=$this->generateProductInvList($locations);
            $form = $this->createForm(AddProductType::class, array('productInventories' => $productInventories, 'productImages' => $productImgs));
//        }
        if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    // handle the form of your type
                    $task = $form->getData();
                    $product=$this->buildProduct($task);
                    $errors=$this->validator->validate($product,null,['need_validation']);
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
                        $this->entityManager->persist($product);

                        //dd($task['productImages']);
                        foreach ($task['productImages'] as $prodImg) {
                            $this->handleImage($prodImg,$this->slugger,$product,$this->entityManager);
                        }


                        foreach ($task['productInventories'] as $pi) {
                            if ($pi->getQuantity() != null) {
                                $pi->setProduct($product);
                                $product->addProductInventory($pi);
                                $this->entityManager->persist($pi);
                                // STOPPED AT VENTS
                            }
                        }
                        // ADD BROCHURE FUNCTION HERE
                        $fileToUpload = $form->get('product')->get('thumbnail')->getData();
                        $newFilepath = $this->uploadFile($fileToUpload, $this->slugger);
                        $product->setThumbnail($newFilepath);
                        // add some checks for product fields
                        $this->entityManager->persist($product);
                        //dd($product);
                        $this->entityManager->flush();
                        return $this->redirectToRoute('app_edit_product',["product_id"=>$product->getUid()], 302);
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
        return $this->render('forms/addproduct.html.twig', [
            'controller_name' => 'AddProductController',
            'form' => $form,
            'session' => $session_arr,
            'productTypes' => $types_arr,
//            'errors' => $form->getErrors(true),
        ]);
    }
    #[Route('/admin/edit/location/{id}', name: 'app_edit_location')]
    public function locationEdit(Request $request,ManagerRegistry $managerRegistry, EntityManagerInterface $entityManager,int $id): Response
    {
        $location=$entityManager->getRepository(Locations::class)->findOneBy(['id'=>$id]);
        if(!$location)
            return new Response("Location not found",404);

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

        return $this->render('forms/addlocation.html.twig', [
            'controller_name' => 'Add Location',
            'form' => $form,
        ]);
    }
    #[Route('/admin/add/location', name: 'app_add_location')]
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

        return $this->render('forms/addlocation.html.twig', [
            'controller_name' => 'Add Location',
            'form' => $form,
        ]);
    }
    #[Route('/admin/edit/product/{product_id}', name: 'app_edit_product')]
    public function editProduct(Request $request,ManagerRegistry $managerRegistry, EntityManagerInterface $entityManager, SluggerInterface $slugger, ValidatorInterface $validator,string $product_id): Response
    {
        $fileSystem=new Filesystem();
        // LOOK INTO WHY UID CHANGES EVEN IF PRODUCT TYPE DOESN'T
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
        // GET PRODUCT
        $product=$managerRegistry->getRepository(Product::class)->findOneBy(['uid'=>$product_id]);
        //dd($product);

        // IF PRODUCT DOESN'T EXIST RETURN 404
        if(!$product){
            return new Response('Product not found',404);
        }
        // ELSE GET ALL LOCATIONS
        $locations=$managerRegistry->getRepository(Locations::class)->findAll();
        $productInvs=$product->getProductInventories();
        $productUploadedImgs=new ArrayCollection();
        $oldProductImgs=clone ($product->getProductImages());
        $productImgs=new ArrayCollection();
        //dd(count($productImgs));
        // TODO: LOOK INTO FETCHING PI
        #TODO: FIX UPLOADED IMAGE MESS
        #dd($productUploadedImgs);
//        $product->getProductImages()->clear();
//        $entityManager->persist($product);
//        $entityManager->flush();
//        dd($product);
        #$entityManager->flush();
        #dd($oldProductImgs->re);
        #dd($oldProductImgs);
        #TODO: FIX VALIDATION, AS IT BYPASSES IT
        foreach ($oldProductImgs as $pi){
            $prodimg= new File($this->getParameter('showcases_directory').'/'.$pi->getPath(),true);
            $productUploadedImgs->add($prodimg);
        }
        #dd($product->getProductImages());
        // GET REMAINING NOT USED PRODUCT IMAGES
        $maxProd=5-$oldProductImgs->count();
        //dd();
        // ADD THEM TO PRODUCTIMGS
        for ($i = 0; $i < $maxProd; $i++) {
            $prodimg = new File('', false);
            $emptyProdImg= new ProductImages();
            $productUploadedImgs->add($prodimg);
            #$productImgs->add($emptyProdImg);
        }
        //dd($i);
        //dd($productImgs);
        //TODO: LOOK INTO PRODUCT INVS, MAKE ALL LOCATION INVENTORIES FOR EACH PRODUCT INSTEAD OF ONLY ONES THAT THEY HAVE INV IN
        foreach($productInvs as $pi){
            for($j=0;$j<count($locations);$j++){
                if($pi->getLocation()==$locations[$j]) {
                    unset($locations[$j]);
                    break;
                }
            }
        }
        //dd($locations);
        // Generate product inv list
        $productInvs=$this->generateProductInvList($locations);
        // Add them to the product
        foreach($productInvs as $pi){ //
            $product->addProductInventory($pi);
        }
        //dd($productInvs);
        $prodClassName=$this->get_class_name(get_class($product));
        $oldProduct=clone $product;
        #dd($oldProduct);
        #$oldProduct->setProductImages($productImgs);
        //$product->setProductImages();
        //dd($product)
        // BUILD FORM AND ASSIGN VALUES
        #dd($product->getProductImages());
        #dd($product);
        $form=$this->createForm(EditProductType::class,[
            'name'=>$product->getName(),
            'shortDesc'=>$product->getShortDesc(),
            'description'=>$product->getDescription(),
            'thumbnail'=>new File($product->getThumbnail()? : '' || $product->getThumbnail(),false),
            'price'=>$product->getPrice(),
            'SKU'=>$product->getSKU(),
            'seller'=>$product->getSeller(),
            'status'=>$product->getStatus(),
            'productInventories'=>$product->getProductInventories(),
            'productImages'=>$productUploadedImgs,
            'product' => $product,
            strtolower($prodClassName) => $product]);
        // IF IT'S A POST REQUEST
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            #dd("dies here??!");
            if ($form->isSubmitted() && $form->isValid()) { // TODO: FIX THE SERIALIZATION ERROR CAUSED BY $form->isValid() from this if
                // handle the form of your type
                $task = $form->getData();
                $needImgVerif=true;
                // IF TYPE DIFFERS, MAKE A NEW PRODUCT OF SAID NEW TYPE

                if(strtolower($task['type'])!=$product->getType()){
                    $newProduct=$this->buildProduct($task);
                    $newProduct->setUid($product->getUid());
                    $needImgVerif=false;
                    //returns empty product if no fields were completed
                }else
                    $newProduct=$product;
                //dd($newProduct);
                // validate the edited product
//                dd("dies here");
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
                elseif($newProduct) { // IF PRODUCT IS NOT A NULL OBJECT FROM NO FIELDS COMPLETED
                    // CONVERT UPLOADEDFILE TO PRODUCTIMAGES
                    $productImgsNew=new ArrayCollection();
                    #dd($task['productImages']);
                    foreach($task['productImages'] as $pi){
                        $newPI=new ProductImages();

                        if($pi!=null){
                            $uploadPath=$pi->getPathname();
                            $newHash=sha1_file($uploadPath);
                            $newPI->setHash($newHash)->setPath($uploadPath)->setProduct($newProduct);
                        }
                        $productImgsNew->add($newPI);
                    }
                    #dd($productImgsNew);
                    #dd($newProduct->getProductImages());
                    $newProduct->setProductImages($productImgsNew);
//                    dd($newProduct,$task['productImages']);
                    if($needImgVerif){
                        // MAKE A NEW COLLECTION CONTAINING NEW IMGS
                        #dd($productImgs);
                        #dd($task['productImages']);
                        #dd($product,$productImgs);
                        foreach ($newProduct->getProductImages() as $index=>$uploadedImg) { #TODO: BEAUTIFY CODE, MOVE THIS TO A FUNCTION INSTEAD
                            if($uploadedImg->getPath()!=null) {
                                $verifyImg=true;
                                $verifyImg = $oldProduct->checkImgAtIndex($uploadedImg, $index);
                                if ($verifyImg) {
                                    # If image already is uploaded, we set the path to it instead of reuploading it
                                    $oldImg = $oldProduct->getProductImages()->get($index);
                                    #dd($oldImg);
                                    $uploadedImg->setPath($this->getParameter('showcases_directory').'/'.$oldImg->getPath());
                                }else
                                {
                                    $this->handleSpecificImage($task['productImages'][$index],$slugger,$newProduct,$index,$entityManager);
                                    $oldImg = $oldProduct->getProductImages()->get($index);
                                    if($oldImg!=null)
                                        $fileSystem->remove($this->getParameter('showcases_directory').'/'.$oldImg->getPath());
                                }
                                #dd($uploadedImg);
                                $entityManager->persist($uploadedImg);
                            }
                        }
                        foreach($newProduct->getProductImages() as $uploadedImg){
                            if($uploadedImg->getPath()==null){
                                $newProduct->getProductImages()->removeElement($uploadedImg);
                                $uploadedImg->setProduct(null);
                            }
                        }
                        #dd($newProduct->getProductImages());
                    }else{
                        foreach($newProduct->getProductImages() as $index=>$uploadedImg){
                            if($uploadedImg->getPath()!=null){
                                $this->handleSpecificImage($task['productImages'][$index],$slugger,$newProduct,$index,$entityManager);
                            }
                            else{
                                $newProduct->getProductImages()->removeElement($uploadedImg);
                                $uploadedImg->setProduct(null);
                            }
                        }
                    }
                    #dd($newProduct);
                    #dd('e',$newProduct->getProductImages());
                    # Verifies image, removes old ones
//                        dd($product);
//                    $newCol=$newProduct->getProductInventories();
//                    dd($newCol);
//                    $newCol->clear();
                    #dd($newProduct->getProductInventories());
                    foreach ($newProduct->getProductInventories() as $pi) {
                        $quantity=$pi->getQuantity();
                        if ($quantity == null || $quantity == 0) {
                            $newProduct->removeProductInventory($pi);
                            $entityManager->remove($pi);
                            // STOPPED AT VENTS
                        }
                        else{
                            $pi->setProduct($newProduct);
                            $pi->setModifiedAt(new \DateTimeImmutable());
                            $entityManager->persist($pi);
                        }
                    }
                   # dd($newProduct);
//                    $product->setProductInventories($newCol);
                    #dd($newProduct->getProductInventories());
                    #dd($newProduct->getThumbnail(),$task['thumbnail']);
                    // ADD BROCHURE FUNCTION HERE
                    if($task['thumbnail'] instanceof UploadedFile){
                        $newThumbnailHash=sha1_file($task['thumbnail']->getRealPath());
                        $thumbnailHash=sha1_file($this->getParameter('thumbnails_directory').'/'.$oldProduct->getThumbnail());
                        # TODO: If you have more free time, turn the thumbnail into a ProductImages (to store hash)
                        if($newThumbnailHash!=$thumbnailHash){
                            $fileToUpload = $task['thumbnail'];
                            $newFilepath = $this->uploadFile($fileToUpload, $slugger);
                        }
                        $product->setThumbnail($newFilepath);
                    }
                    // add some checks for product fields
                    //dd($newProduct,$product);
                    if($newProduct->getId()!=$oldProduct->getId()){
                        # IF NEW PRODUCT IS ACTUALLY A NEWLY CREATED ONE, REMOVE THE OLD PRODUCT
                        # TODO: TEST IF ACTUALLY REMOVES IMAGES
                        dd("happen");
                        $entityManager->persist($newProduct);
                        $newProduct->setUid($oldProduct);
                        foreach($oldProduct->getProductInventories() as $pi)
                            $entityManager->remove($pi); // CHECK RESTRICTIONS
                        foreach($oldProduct->getProductImages() as $pim){
                            if($pim->getPath()!=null){
                                $fileSystem->remove($this->getParameter('showcases_directory').'/'.$pim->getPath()); // NOT SURE IF CORRECT
                            }
                            $entityManager->remove($pim);
                        }
                        $fileSystem->remove($this->getParameter('thumbnails_directory').'/'.$oldProduct->getThumbnail());
                        $entityManager->remove($oldProduct);
                    }
                    #dd($newProduct);
                    #dd($newProduct);
                    #$entityManager->persist($newProduct);
                    $entityManager->flush();
                    $this->redirectToRoute('app_edit_product',['product_id'=>$newProduct->getUid()]);
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

        return $this->render('forms/editproduct.html.twig', [
            'controller_name' => 'Edit Product',
            'form' => $form,
            'locations' => $locations,
//            'session' => $session_arr,
            'productTypes' => $types_arr,
            'product' => $product,
            'productUploadedImages' => $productUploadedImgs,
        ]);
    }
    #[Route('/admin/delete/product/{product_id}', name: 'app_delete_product')]
    public function deleteProduct(Request $request, EntityManagerInterface $entityManager, string $product_id){
        $product=$entityManager->getRepository(Product::class)->findOneBy(['uid'=>$product_id]);
        if($product){
            $entityManager->remove($product);
            $entityManager->flush();
            $route = $request->headers->get('referer');
            if($route)
                return $this->redirect($route);
            else return $this->redirectToRoute('app_admin',[],302);
        }
        else return new Response("Product not found", 404);
    }
    #[Route('/admin/delete/location/{id}', name: 'app_delete_location')]
    public function deleteLocation(Request $request, EntityManagerInterface $entityManager, int $id){
        $location=$entityManager->getRepository(Locations::class)->findOneBy(['id'=>$id]);
        if($location){
            $entityManager->remove($location);
            $entityManager->flush();
            $route = $request->headers->get('referer');
            if($route)
                return $this->redirect($route);
            else return $this->redirectToRoute('app_admin',[],302);
        }
        else return new Response("Location not found", 404);
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
        $product->setShortDesc($task['shortDesc']);
        $product->setDescription($task['description']);
        $product->setSKU($task['SKU']);
        $product->setSeller($task['seller']);
        $product->setPrice($task['price']);
        $product->setStatus($task['status']);
//        dd($product);


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
            $newHash=sha1_file($this->getParameter('showcases_directory').'/'.$newFilepath);
            $tempProdImg = new ProductImages();
            $tempProdImg->setPath($newFilepath)->setHash($newHash)->setProduct($product);
            $product->addProductImage($tempProdImg);
            $entityManager->persist($tempProdImg);
        }
    }
    private function handleSpecificImage(?UploadedFile $prodImg,SluggerInterface $slugger, Product $product,int $index, EntityManagerInterface $entityManager){
        if ($prodImg != null) {
            $newFilepath = $this->uploadFile($prodImg, $slugger, 'showcases_directory');
            $newHash = sha1_file($this->getParameter('showcases_directory').'/'.$newFilepath);
            $newProdImg=$product->getProductImages()->get($index);
            $newProdImg->setPath($newFilepath)->setHash($newHash)->setProduct($product);
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
            $pi->setQuantity(0);
            $pi->setModifiedAt(DateTimeImmutable::createFromMutable(new DateTime()));
            $pi->setCreatedAt(DateTimeImmutable::createFromMutable(new DateTime()));
            //$location->addProductInventories($pi);
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
