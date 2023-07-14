<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Locations;
use App\Entity\Option;
use App\Entity\Product;
use App\Entity\ProductImages;
use App\Entity\ProductInventory;
use App\Form\AddProductType;
use App\Form\CoolerType;
use App\Form\CpuType;
use App\Form\EditProductType;
use App\Form\GpuType;
use App\Form\HddType;
use App\Form\LocationsType;
use App\Form\MemoryType;
use App\Form\MotherboardType;
use App\Form\PccaseType;
use App\Form\PsuType;
use App\Form\SsdType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

            $productInventories=$this->generateProductInvList($locations);
            $form = $this->createForm(AddProductType::class, array('productInventories' => $productInventories, 'productImages' => $productImgs, 'type'=>1));

        if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    // handle the form of your type
                    $task=$form->getData();

                    $product=$this->buildProduct($task);

                    foreach ($task['productImages'] as $prodImg) {
                        $this->handleImage($prodImg,$this->slugger,$product,$this->entityManager);
                    }

                    // ADD INVENTORIES NO MATTER WHAT FOR REFUND FUNCTION LATER
                    foreach ($task['productInventories'] as $pi) {
                            $pi->setProduct($product);
                            $product->addProductInventory($pi);
                            $this->entityManager->persist($pi);
                            // STOPPED AT VENTS
                    }
                    // ADD BROCHURE FUNCTION HERE
                    $fileToUpload = $form->get('product')->get('thumbnail')->getData();
                    $newFilepath = $this->uploadFile($fileToUpload, $this->slugger);
                    $product->setThumbnail($newFilepath);
                    // add some checks for product fields
                    $this->entityManager->persist($product);

                    $this->entityManager->flush();
                    return $this->redirectToRoute('app_admin');
                }

        }
        return $this->render('forms/addproduct.html.twig', [
            'controller_name' => 'AddProductController',
            'form' => $form,
            'session' => $session_arr,
            'productTypes' => $types_arr,
             'type' => 'add',
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
                $entityManager->persist($location);
                $entityManager->flush();
            }
        }

        return $this->render('forms/addlocation.html.twig', [
            'controller_name' => 'Add Location',
            'form' => $form,
        ]);
    }
    #[Route('/admin/render_subform', name: 'app_admin_render_subform')]
    public function renderSubform(Request $request): Response
    {
        $formType=$request->get('add_or_edit');
        if($formType=='add'){
            $form=$this->createForm(AddProductType::class);
        }else
            $form=$this->createForm(EditProductType::class);

        $subforms=[
            GpuType::class,
            CpuType::class,
            MemoryType::class,
            MotherboardType::class,
            SsdType::class,
            PsuType::class,
            PccaseType::class,
            CoolerType::class,
            HddType::class
        ];

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $type=$request->get('type');

            $form->remove('embeddedForm');
            $form->add('embeddedForm',$subforms[$type-1]);
        }

        return $this->render('forms/subform_template.html.twig', [
            'subform' => $form->get('embeddedForm'),
            'subform_id'=> $type,
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

        foreach ($oldProductImgs as $pi){
            $prodimg= new File($this->getParameter('showcases_directory').'/'.$pi->getPath(),true);
            $productUploadedImgs->add($prodimg);
        }

        // GET REMAINING NOT USED PRODUCT IMAGES
        $maxProd=5-$oldProductImgs->count();
        // ADD THEM TO PRODUCTIMGS
        for ($i = 0; $i < $maxProd; $i++) {
            $prodimg = new File('', false);
            $emptyProdImg= new ProductImages();
            $productUploadedImgs->add($prodimg);
        }

        foreach($productInvs as $pi){
            foreach($locations as $key => $location){
                if($pi->getLocation()==$location) {
                    unset($locations[$key]);
                    break;
                }
            }
        }

        // Generate product inv list
        $productInvs=$this->generateProductInvList($locations);
        // Add them to the product
        foreach($productInvs as $pi){ //
            $product->addProductInventory($pi);
        }

        $prodCategory=$product->getCategory();
        $FormPath="App\\Form\\";
        $embeddedFormType=$FormPath.ucfirst($prodCategory->getCategoryName())."Type";
        $prodOptions=[];
        foreach($product->getOptions() as $option){
            $prodOptions[$option->getOptionName()] = $option->getOptionValue();
        }
        $oldProduct=clone $product;

        // BUILD FORM AND ASSIGN VALUES
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
             'type'=> $prodCategory->getId(),
            'oldType' => $prodCategory->getId(),
            'embeddedForm' => $prodOptions,
            'eventCheck'=>0,
            ],['form_type'=>$embeddedFormType]);
        // IF IT'S A POST REQUEST
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // handle the form of your type
                $task = $form->getData();
                $needImgVerif=true;
                // IF TYPE DIFFERS, REMOVE OLD OPTIONS AND REPLACE THEM WITH NEW ONES
                $product->setName($task['name']);
                $product->setShortDesc($task['shortDesc']);
                $product->setDescription($task['description']);
                $product->setPrice($task['price']);
                $product->setSKU($task['SKU']);
                $product->setSeller($task['seller']);
                $product->setStatus($task['status']);

                if($task['type']!=$prodCategory->getId()){

                    foreach($oldProduct->getOptions() as $option){
                        $product->removeOption($option);
                        $option->setProduct(null);
                        $this->entityManager->remove($option);
                    }
                    $newProduct=$this->changeProductCategory($product,$task);
                    $needImgVerif=false;
                    //returns empty product if no fields were completed
                }else{
                    $newProduct=$product;
                    $optionArray=$task['embeddedForm'];
                    foreach($optionArray as $option_name=>$option_value){
                        $notFound=true;
                        foreach($newProduct->getOptions() as $option){
                            if($option->getOptionName()==$option_name){
                                $notFound=false;
                            }
                        }
                        if($notFound){
                            $newOption=new Option();
                            $newOption->setOptionName($option_name);
                            $newOption->setOptionValue($option_value);
                            $newProduct->addOption($newOption);
                            $this->entityManager->persist($newOption);
                        }
                    }
                    foreach($newProduct->getOptions() as $option){
                        $newOptionValue=$optionArray[$option->getOptionName()];
                        $option->setOptionValue($newOptionValue);
                    }
                }

                // validate the edited product
                    // CONVERT UPLOADEDFILE TO PRODUCTIMAGES
                    $productImgsNew=new ArrayCollection();

                    foreach($task['productImages'] as $pi){
                        $newPI=new ProductImages();

                        if($pi!=null){
                            $uploadPath=$pi->getPathname();
                            $newHash=sha1_file($uploadPath);
                            $newPI->setHash($newHash)->setPath($uploadPath)->setProduct($newProduct);
                        }
                        $productImgsNew->add($newPI);
                    }

                    $newProduct->setProductImages($productImgsNew);

                    if($needImgVerif){
                        // HANDLE IMAGE VERIFICATION
                        foreach ($newProduct->getProductImages() as $index=>$uploadedImg) {
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

                                $entityManager->persist($uploadedImg);
                            }
                        }
                        foreach($newProduct->getProductImages() as $uploadedImg){
                            if($uploadedImg->getPath()==null){
                                $newProduct->getProductImages()->removeElement($uploadedImg);
                                $uploadedImg->setProduct(null);
                            }
                        }

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

                    foreach ($newProduct->getProductInventories() as $pi) {
                        $quantity=$pi->getQuantity();
                        if ($quantity == null || $quantity == 0) {
                            $newProduct->removeProductInventory($pi);
                            $entityManager->remove($pi);
                        }
                        else{
                            $pi->setProduct($newProduct);
                            $pi->setModifiedAt(new \DateTimeImmutable());
                            $entityManager->persist($pi);
                        }
                    }

                    // ADD BROCHURE FUNCTION HERE
                    if($task['thumbnail'] instanceof UploadedFile){
                        $newThumbnailHash=sha1_file($task['thumbnail']->getRealPath());
                        if($oldProduct->getThumbnail()!=null)
                            $thumbnailHash=sha1_file($this->getParameter('thumbnails_directory').'/'.$oldProduct->getThumbnail());
                        else
                            $thumbnailHash=null;

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
            'type'=>'edit',
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

    private function setProductProperties($product,$task,$category){
        $product->setName($task['name']);
        $product->setShortDesc($task['shortDesc']);
        $product->setDescription($task['description']);
        $product->setSKU($task['SKU']);
        $product->setSeller($task['seller']);
        $product->setPrice($task['price']);
        $product->setStatus($task['status']);
        $product->setCategory($category);
    }
    private function buildProduct($task): ?Product{
//        $optionArray=[
//            #"gpu"
//            1 => ["interface","series","clock","memory_type","memory_size"],
//            #"cpu"
//            2 => ["socket","series","core","frequency"],
//            #"memory"
//            3 => ["memory_type","memory_size","frequency","latency"],
//            #"motherboard"
//            4 => ["format","socket","chipset_producer","chipset_model","interface","memory_type","tech"],
//            #"ssd"
//            5 => ["series","interface","size","max_reading"],
//            #"psu"
//            6 => ["power","vent","pfc","efficiency","certification"],
//            #"pccase"
//            7 => ["type","height","diameter","width","slots"],
//            #"coolers"
//            8 => ["type","cooling","height","vents","width"],
//            #"hdd"
//            9 => ["series","interface","size","reading_speed","buffer_size"],
//        ];

        $product=new Product();
        $category_id=$task['type'];
        try {
            $category = $this->entityManager->getReference(Category::class, $category_id);
        } catch (ORMException $e) {
            return $product;
        }

        $this->setProductProperties($product,$task,$category);

        //dd($task);
        foreach($task["embeddedForm"] as $key => $value){
            $optionObj=new Option();
            $optionObj->setProduct($product);
            $optionObj->setOptionName($key);
            $optionObj->setOptionValue($value);
            $product->addOption($optionObj);
            $this->entityManager->persist($optionObj);
        }

        $uuid = Uuid::v4();
        $product->setUid($uuid);
        $product->setCreatedAt(DateTimeImmutable::createFromMutable(new DateTime()));

        return $product;
    }
    private function changeProductCategory($product,$task): ?Product{

        $category_id=$task['type'];
        try {
            $category = $this->entityManager->getReference(Category::class, $category_id);
        } catch (ORMException $e) {
            return $product;
        }

        $product->setCategory($category);

        //dd($task);
        foreach($task["embeddedForm"] as $key => $value){
            $optionObj=new Option();
            $optionObj->setProduct($product);
            $optionObj->setOptionName($key);
            $optionObj->setOptionValue($value);
            $product->addOption($optionObj);
            $this->entityManager->persist($optionObj);
            # TODO: modify add/edit form for this, maybe readd vents. Focus on fixing everything using products
        }

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
