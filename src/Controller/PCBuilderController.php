<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\Locations;
use App\Entity\Option;
use App\Entity\PCBuilderTemplate;
use App\Entity\Product;
use App\Entity\ProductInventory;
use App\Form\PCBuilderType;
use DeepCopy\f001\B;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Tests\Models\Taxi\Car;
use Monolog\DateTimeImmutable;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\isEmpty;

class PCBuilderController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private array $quickAdvice;
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->quickAdvice = [
            'gpu'=>"This part and the CPU should together be at least 70% of your budget's income most of the times when building a PC. For a GPU, focus on the clock speed, memory size and type, as they're the most important when it comes to a card's performance.",
            'cpu'=>"This and your GPU should together be at least 70% of your budget's income most of the times when building a PC. Focus on frequency and number of cores when it comes to assessing performance. Intel tends to perform better but is expensive, although AMD processors have been catching up performance-wise with more affordable prices. Make sure the socket is compatible with the one the motherboard you're using has.",
            'memory'=>"For the RAM, you should focus on memory size and frequency. Memory type also matters not only for performance, but also for compatibility. Make sure the memory type is the same as the motherboard, otherwise it might risk not being compatible.",
            'motherboard'=>'This is a really important part when it comes to compatibility. It can restrict your choice when it comes to processors and RAM. Make sure you find CPUs with the same socket it is compatible with and RAM with the same memory type.',
            'ssd'=>"This is where all your data is stored, including your OS, programs and files. It's newer, faster, but more expensive. The speed and size are important for performance.",
            'pccase'=>"The size depends on the components you're using. The bigger the better for storing everything, some components may even require it, but remember that you have to store your case somewhere as well.",
            'hdd'=>"This is where all your data is stored, including your OS, programs and files. It's slower than an SSD, but more affordable. The speed and size are important for performance.",
            'psu'=>"This is used to power up your components. The best choice would be a PSU which is greater by at least 200 W than the total consumption of components.",
            'cooler'=>"This is used usually for cooling the CPU and keeping the airflow in the case. Make sure it fits in your case and make sure you get at least one.",
        ];
    }
    #[Route('/pcbuilder', name: 'app_pcbuilder_choice')]
    public function pcbuilder_choice(): Response
    {
        return $this->render('products/pcbuilder_choice.html.twig', [
            'controller_name' => 'PCBuilderController',
        ]);
    }

    #[Route('/pcbuilder/generate_tbob', name: 'app_pcbuilder_generate_tbob')]
    public function generate_tbob(Request $request): Response
    {
        // CATEGORY NAMES USED IN BUILDER (currently not using PSU/COOLER)
        $category_names=[
            'gpu','cpu','memory','motherboard','ssd','pccase','hdd',
        ];
        $income=$request->get('income');
        if($income==null){
            $income=2000;
        }
        $uid=$request->get('uid');
        $builderTemplate=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(['uid'=>$uid]);
        $generatedBuild=$this->calculateBuildForNow($income,$builderTemplate);
        foreach($builderTemplate->getCartItems() as $ci){
            $category_id=$ci->getProduct()->getCategory()->getId();
            if(key_exists($category_id,$generatedBuild)){
                unset($generatedBuild[$category_id]);
            }
        }
        $this->createCartItemsForBuildTemplate($generatedBuild[0],$builderTemplate);
        $categories=$this->entityManager->getRepository(Category::class)->findAll();
        $selectedProducts=$this->getSelectedProducts($builderTemplate);
        $consumption_total=$builderTemplate->getConsumptionTotal();
        $psuAdvice=$this->getPSUAdvice($builderTemplate,$consumption_total);
        $compatibility_problems=$this->getCompatibilityAdvice($builderTemplate);
        $msg='';
        if(count($selectedProducts)<7){
            foreach($category_names as $category_name){
                if(!key_exists($category_name,$selectedProducts)){
                    $msg.="Could not find any compatible items in budget for category ".$category_name.". ";
                }
            }
        }
        else{
            $msg='Successfully found all items. You only have to find a PSU and Cooler to finish the set! Feel free to find alternative items too if you are not satisfied!';
        }
        $html=$this->renderView('element_templates/pcbuilder_offcanvas_products_table.html.twig', [
            'categories'=>$categories,
            'selectedProducts'=>$selectedProducts,
            'consumption_total'=>$consumption_total,
            'price_total'=>$builderTemplate->getTotal(),
            'psu_advice'=>$psuAdvice,
            'compatibility_problems'=>$compatibility_problems,
        ]);
        $this->entityManager->flush();
        return $this->json(['html'=>$html,'msg'=>$msg]);

    }
    #[Route('/pcbuilder/generate_template', name: 'app_pcbuilder_generate_template')]
    public function generate_template(Request $request): Response
    {
        $choice_type=$request->get('choice_type');
        switch($choice_type){
            case "blank":
                $builderTemplate=$this->createBuilderTemplate();
                $uuid=$builderTemplate->getUid();
                $route=$this->generateUrl('app_pcbuilder', ['uid'=>$uuid], true );
                return $this->json(["route"=>$route]);
                break;
            case "budget":
                $income=$request->get("income");
                $builds=$this->calculateBuildForNow($income);
                $total_prices=array();
                foreach($builds as $build){
                    $sum=0;
                    foreach($build as $product){
                        if($product!=null)
                            $sum+=$product->getPrice();
                    }
                    $total_prices[]=$sum;
                }
                $html=$this->renderView('element_templates/pcbuilder_choice_builds.html.twig', [
                    'builds' => $builds,
                    'total_prices' => $total_prices,
                ]);
                return $this->json(['html'=>$html]);
                break;
            case "selectedBuild":
                $parts_uid_arr=$request->get("parts");
                $parts=$this->entityManager->getRepository(Product::class)->findBy(['uid'=>$parts_uid_arr]);
                $builderTemplate=$this->createBuilderTemplate($parts);
                $uuid=$builderTemplate->getUid();
                $route=$this->generateUrl('app_pcbuilder', ['uid'=>$uuid], true );
                return $this->json(["route"=>$route]);
                break;
            default:
                return $this->json(['error'=>404]);
                break;
        }

    }
    public function createBuilderTemplate($parts=array()){
        $builderTemplate=new PCBuilderTemplate();
        $uuid = Uuid::v4();
        $builderTemplate->setUid($uuid);
        $user=$this->getUser();
        // TODO: ADD CREATION DATE FOR NOT LOGGED USER
        if($user) {
            $builderTemplate->setOwningUser($user);
            $builderTemplate->setTemplateName($user->getFirstName()."'s Template");
            $builderTemplate->setTemplateDescription("Example description here!");
        }
        else{
            $builderTemplate->setTemplateName('Example Template');
            $builderTemplate->setTemplateDescription("Example description here!");
        }
        $this->createCartItemsForBuildTemplate($parts,$builderTemplate);
        $this->entityManager->persist($builderTemplate);
        $this->entityManager->flush();
        return $builderTemplate;
    }
    public function createCartItemsForBuildTemplate($parts,&$builderTemplate){
        if(count($builderTemplate->getCartItems())>0){
            foreach($builderTemplate->getCartItems() as $cartItem){
                $builderTemplate->removeCartItem($cartItem);
                $this->entityManager->remove($cartItem);
            }
            $this->entityManager->flush();
        }
        foreach($parts as $part){
            if($part!=null){
                $cartItem=new CartItem();
                $cartItem->setProduct($part)->setQuantity(1)->setAssocOrder(null);
                $this->entityManager->persist($cartItem);
                $builderTemplate->addCartItem($cartItem);
            }
        };
    }
    #[Route('/pcbuilder/build/{uid}', name: 'app_pcbuilder')]
    public function pcbuilder($uid): Response
    {
        // TODO: FINISH UP TEMPLATE PAGE TOO
        $pcbuildertemplate = $this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(['uid'=>$uid]);
        if($pcbuildertemplate==null){
            return new Response("Template not found!",404);
        }
        if($this->getUser()->getId()!=$pcbuildertemplate->getOwningUser()->getId() && !$this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_pcbuilder_choice');
        }
        $categories=$this->entityManager->getRepository(Category::class)->findAll();
        $selectedProducts=$this->getSelectedProducts($pcbuildertemplate);
        $form = $this->createForm(PCBuilderType::class,$pcbuildertemplate);

        $consumption_total=$pcbuildertemplate->getConsumptionTotal();
        $psuAdvice=$this->getPSUAdvice($pcbuildertemplate,$consumption_total);
        $compatibility_problems=$this->getCompatibilityAdvice($pcbuildertemplate);


        return $this->render('products/pcbuilder.html.twig', [
            'controller_name' => 'PCBuilderController',
            'form' => $form,
            'categories' => $categories,
            'selectedProducts' => $selectedProducts,
            'uid' => $uid,
            'price_total' => $pcbuildertemplate->getTotal(),
            'consumption_total' => $consumption_total,
            'psu_advice'=>$psuAdvice,
            'quick_advice'=>$this->quickAdvice,
            'compatibility_problems'=>$compatibility_problems,
        ]);
    }
    #[Route('/pcbuilder/fetch_products', name: 'app_pcbuilder_fetch_products')]
    public function fetch_products(Request $request): Response
    {
        $selected_filters_by_category= [
            "gpu" =>  ["memory_type"],
            "cpu" =>  ["socket"],
            "memory" => ["memory_type"],
            "motherboard" => ["socket", "memory_type"],
            //"psu" => ["consumption"]
        ];
        $category_id = $request->get('category_id');
        $selected_product = $request->get('selected_product');
        $template_uid = $request->get('template_uid');
        $category_mode = 0;
        $category = $this->entityManager->getReference(Category::class,$category_id);
        $category_name=$category->getCategoryName();

        $template=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(['uid'=>$template_uid]);
        $selected_filter_arr=array();

        if(key_exists($category_name,$selected_filters_by_category)){

            $this->populateSelectedCategoryFiltersArray($template,$selected_filters_by_category[$category_name],$selected_filter_arr);
        }

        $filters=$this->getFilterArrayByCategoryId($category_id);

        foreach($selected_filter_arr as $filter_name=>$filter_val_arr){
            foreach($filter_val_arr as $f_key => $f_val) {
                if(!$this->searchFilterArraysForValue($filters,$filter_name,$f_val)){
                    if(count($selected_filter_arr[$filter_name])>1)
                        unset($selected_filter_arr[$filter_name][$f_key]);
                    else
                        unset($selected_filter_arr[$filter_name]);
                }
            }
        }

        if(count($selected_filter_arr)>0)
            $products= $this->entityManager->getRepository(Product::class)->findByFiltersAndFunctionValue($selected_filter_arr,$category_mode,$category_id);
        else
            $products=$this->entityManager->getRepository(Product::class)->findBy(['category'=>$category_id,'status'=>1]);

        $product_table=$this->renderView('element_templates/product_table.html.twig', [
            'products'=>$products,
            'selected_product'=>$selected_product,
        ]);
        $filter_list=$this->renderView('element_templates/filter_list.html.twig',['filters'=>$filters,'function'=>0,'value'=>$category_id]);
        return $this->json(['product_table'=>$product_table,'filter_list'=>$filter_list,'selected_filters'=>$selected_filter_arr]);
    }
    #[Route('/pcbuilder/apply_filter/', name:'app_pcbuilder_apply_filters')]
    public function pcbuilder_apply_filter(Request $request): Response
    {
        $filter_array = $request->get('filter_array');
        $function_used = $request->get('function');
        $search_value = $request->get('value');
        $selected_product=$request->get('selected_product');

        $obj = Product::class;
        if(empty($filter_array)){
            $filter_array=[];
        }
        $products= $this->entityManager->getRepository($obj)->findByFiltersAndFunctionValue($filter_array,$function_used,$search_value);

        $html = $this->renderView('element_templates/product_table.html.twig',['products'=>$products,'selected_product'=>$selected_product]);

        return $this->json($html);
    }
    public function getFilterArrayByCategoryId($category_id){
        $filters_ungrouped=$this->entityManager->getRepository(Product::class)->findFiltersByCategoryId($category_id);
        return $this->groupFiltersByColumn($filters_ungrouped);
    }
    /*
     * Description: Groups the ungrouped filters in a filter array used to populate
     * the filter list.
     * */
    public function groupFiltersByColumn($filters_ungrouped){
        $filters_grouped=[];
        foreach($filters_ungrouped as $filter){
            $filters_grouped[$filter['option_name']][]=[$filter['option_value'],$filter['option_count']];
        }
        return $filters_grouped;
    }
    #[Route('/pcbuilder/add_component', name: 'app_pcbuilder_add_component')]
    public function add_component(Request $request): Response
    {
        $component_id=$request->get('newComponent');
        $template_uid=$request->get('uid');
        $currentTemplate=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(["owningUser"=>$this->getUser()->getId(),"uid"=>$template_uid]);
        $newComponent=$this->entityManager->getReference(Product::class,$component_id);
        $component=null;
        $ok=false;
        foreach($currentTemplate->getCartItems() as $item){
            if($item!=null)
                if($item->getProduct()->getCategory()==$newComponent->getCategory()){
                    if($item->getProduct()->getId()==$newComponent->getId()){ // IF ITEM IS THE SAME AS THE COMPONENT ADDED WE INCREASE THE QUANTITY
                        $item->setQuantity($item->getQuantity()+1);
                        $component=$item;
                        break;
                    }else{
                    $item->setProduct($newComponent);
                    $item->setQuantity(1);
                    $this->entityManager->persist($item);
                    $component=$item;
                    $ok=true;
                    break;
                    }
                };
        }
        if($ok==false){
            $item=new CartItem();
            $item->setProduct($newComponent);
            $item->setQuantity(1);
            $this->entityManager->persist($item);
            $component=$item;
            $currentTemplate->addCartItem($item);
        }

        $this->entityManager->flush();
        $compatibility_problems=$this->getCompatibilityAdvice($currentTemplate);
        $html= $this->renderItem($component,$compatibility_problems);


        return $this->renderSelected($currentTemplate,$html,$compatibility_problems);
        }
    #[Route('/pcbuilder/remove_component', name: 'app_pcbuilder_remove_component')]
    public function remove_component(Request $request): Response
    {
        $component_id=$request->get('component_id');
        $template_uid=$request->get('uid');
        $currentTemplate=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(["owningUser"=>$this->getUser()->getId(),"uid"=>$template_uid]);
        $deleted_cart_item=null;
        foreach($currentTemplate->getCartItems() as $cartItem){
            if($cartItem->getProduct()->getId()==$component_id)
            $deleted_cart_item=$cartItem;
        }
        if($deleted_cart_item==null){
            return new Response("CartItem not found",404);
        }

        $category=$deleted_cart_item->getProduct()->getCategory()->getCategoryName();
//        dd($deleted_cart_item);
        $currentTemplate->removeCartItem($deleted_cart_item);
        $this->entityManager->remove($deleted_cart_item);
        $this->entityManager->flush();
        $html=$this->renderView('element_templates/pcbuilder_empty_products.html.twig',['category_type'=>$category]);
        $compatibility_problems=$this->getCompatibilityAdvice($currentTemplate);
        return $this->renderSelected($currentTemplate,$html,$compatibility_problems);
    }

    private function renderSelected($currentTemplate,$html,$compatibility_problems){
        $consumption_total=$currentTemplate->getConsumptionTotal();
        $psuAdvice=$this->getPSUAdvice($currentTemplate,$consumption_total);

        return $this->json(['html'=>$html,'total'=>$currentTemplate->getTotal(),'psu_advice'=>$psuAdvice,'consumption_total'=>$consumption_total,'problems'=>$compatibility_problems]);

    }
    private function renderItem($component,$compatibility_problems){
        $category_id=$component->getProduct()->getCategory()->getId();
        if(array_key_exists($category_id,$compatibility_problems)){
            return $this->renderView('element_templates/pcbuilder_selected_products.html.twig', [
                'item'=>$component,
                'problem'=>$compatibility_problems[$category_id],
            ]);
        }
        else{
            return $this->renderView('element_templates/pcbuilder_selected_products.html.twig', [
                'item'=>$component,
            ]);
        }
    }

    #[Route('/pcbuilder/rename_template', name: 'app_pcbuilder_rename_template')]
    public function rename_template(Request $request): JsonResponse
    {
        $new_name=$request->get('new_name');
        $template_uid=$request->get('uid');
        $currentTemplate=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(["owningUser"=>$this->getUser()->getId(),"uid"=>$template_uid]);

        $errors = $this->validator->validate($new_name,[new NotBlank()]);
        if(count($errors)>0){
            $errorsString = (string) $errors;

            return new JsonResponse(['ok'=>1,'data'=>$errorsString]);
        }

        $currentTemplate->setTemplateName($new_name);
        $this->entityManager->flush();
        return new JsonResponse(['ok'=>0]);
    }
    #[Route('/pcbuilder/update_description', name: 'app_pcbuilder_update_description')]
    public function update_description(Request $request): JsonResponse
    {
        $new_desc=$request->get('new_desc');
        $template_uid=$request->get('uid');
        $currentTemplate=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(["owningUser"=>$this->getUser()->getId(),"uid"=>$template_uid]);

        $errors = $this->validator->validate($new_desc,[new NotBlank()]);
        if(count($errors)>0){
            $errorsString = (string) $errors;

            return new JsonResponse(['ok'=>1,'data'=>$errorsString]);
        }

        $currentTemplate->setTemplateDescription($new_desc);
        $this->entityManager->flush();
        return new JsonResponse(['ok'=>0]);
    }
    #[Route('/template/{uid}', name: 'app_pcbuilder_template')]
    public function view_template($uid): Response
    {
        $template=$this->entityManager->getRepository(PCBuilderTemplate::class)->findOneBy(['uid'=>$uid]);
        return $this->render('element_templates/template_page.html.twig', [
            'controller_name' => 'PCBuilderController',
            'template'=>$template,
        ]);
    }
    /*
     * Description: Looks if the selected item contains any of the possible filters then adds them to the selected filters
     * */
    public function arrangeSelectedFilters($selectedItem,$possible_filters_arr,&$selected_filter_arr){
        foreach($selectedItem->getOptions() as $option){
            $optionName=$option->getOptionName();
            if(in_array($optionName,$possible_filters_arr)){
                $optionValue=$option->getOptionValue();
                if(key_exists($optionName,$selected_filter_arr)) {
                    array_push($selected_filter_arr[$optionName], $optionValue);
                }
                else
                    $selected_filter_arr[$optionName]=[$option->getOptionValue()];
            }
        }
    }
    /*
     * Description: Populates the selected filters array with any of the possible filters found for each product in the template
     * */
    public function populateSelectedCategoryFiltersArray($template,$possible_filters_arr,&$selected_filter_arr){
        foreach($template->getCartItems() as $selectedItem){
            $this->arrangeSelectedFilters($selectedItem->getProduct(),$possible_filters_arr,$selected_filter_arr);

            }

    }
    public function searchFilterArraysForValue($filters,$column_name,$value){
        foreach($filters[$column_name] as $subarray){
            //dd($subarray);
            if ($subarray[0]==$value) {
                return true;
            }
        }
        return false;
    }
    public function PSUAdvise($psu_power,$consumption_total){
        $psu_advice_arr=[
            "perfect" => "The PSU should handle the selected components with no issues!",
            "good" => "The PSU has more than enough power to supply to all components selected, but for safety's sake we would recommend around 200 W of headroom if you can afford it.",
            "ok" => "The PSU has enough power to supply the selected components, but we recommend around 100-200 W of headroom just to be safe.",
            "bad" => "The PSU does not have enough power to supply the selected components! We recommend you choose another PSU that has enough power to handle the power consumption total!"
        ];
        $power_diff=$psu_power-$consumption_total;
        if($power_diff>200){
            return $psu_advice_arr['perfect'];
        }elseif($power_diff>100){
            return $psu_advice_arr['good'];
        }elseif($power_diff<0){
            return $psu_advice_arr['bad'];
        }else{
            return $psu_advice_arr['ok'];
        }
    }
    public function getPSUPowerOption($psu){
        $powerOptionName="power";
        foreach($psu->getOptions() as $option){
            if($option->getOptionName()==$powerOptionName){
                return $option;
            }
        }
    }

//    #[Route('/calc_build/{income}', name: 'app_pcbuilder_calculate_build')]
    public function calculateBuild(mixed $income)
    {
        // PERCENTAGES FOR GPU, CPU, RAM, STORAGE, CASE, PSU in that order
        $builds=[
            [0.35,0.20,0.08,0.10,0.10,0.06],
            [0.30,0.20,0.08,0.10,0.10,0.06],
            [0.30,0.25,0.08,0.10,0.10,0.06],
            [0.30,0.20,0.08,0.10,0.10,0.06],
        ];
        $categories=[
//            4,
            1,2,3,9,5,7,
//            "motherboard",
//"gpu","cpu","memory","hdd","ssd","pccase",
        ];
        // SEPARATE PERCENTAGES FOR MOTHERBOARDS FOR EACH BUILD, USED AFTER PICKING THE MAIN COMPONENTS
        $mb_percentages=
            [0.06,0.06,0.06,0.06];
        $build_arr=[];

        foreach($builds as $build_key=>$build_percentages){
            $money_per_parts=array();
            foreach($build_percentages as $percentage)
                $money_per_parts[]=$percentage*$income;
            $build=new PCBuilderTemplate();
            $best_part_array=array();
            foreach($money_per_parts as $key=>$part_income){
                $parts=$this->entityManager->getRepository(Product::class)->findItemsInCategoryUnderPrice($categories[$key],$part_income);
                $bestPart=$this->getBestPart($parts);
                $best_part_array[$categories[$key]]=$bestPart;
            }
            $mb_filters=[];
            if($best_part_array[2]!=null) {
                $mb_cpu_common_filter = $this->getFilter("socket", $best_part_array[2]);
                $mb_filters[$mb_cpu_common_filter->getOptionName()] = $mb_cpu_common_filter->getOptionValue();
            }
            if($best_part_array[3]!=null) {
                $mb_gpu_common_filter = $this->getFilter("memory_type", $best_part_array[3]);
                $mb_filters[$mb_gpu_common_filter->getOptionName()] = $mb_gpu_common_filter->getOptionValue();
            }

            $parts=$this->entityManager->getRepository(Product::class)->findItemsInCategoryUnderPrice(4,$mb_percentages[$build_key]*$income,$mb_filters);

            if(count($parts)>0)
                $best_part_array[4]=$this->getBestPart($parts);
            $build_arr[]=$best_part_array;
        }
        return $build_arr;
    }

    private function setProductProperties($product,$category,$count){

        $product->setName("Product #".$count);
        $product->setShortDesc("This is a quick made short desc!");
        $product->setDescription("Hello world!");
        $product->setSKU("prod-cat-".$category->getId()."-no-".$count);
        $product->setSeller("Test");
        $product->setPrice(random_int(100,1300));
        $product->setStatus(1);
        $product->setCategory($category);
    }
    private function build100Products(): ?array{
        $optionArray=[
            #"gpu"
            1 => ["interface","series","clock","memory_type","memory_size","consumption"],
            #"cpu"
            2 => ["socket","series","core","core_number","frequency","consumption"],
            #"memory"
            3 => ["memory_type","memory_size","mem_frequency","latency","consumption"],
            #"motherboard"
            4 => ["format","socket","chipset_producer","chipset_model","interface","memory_type","tech","consumption"],
            #"ssd"
            5 => ["series","interface","size","max_reading","consumption"],
            #"psu"
            6 => ["power","vent","pfc","efficiency","certification"],
            #"pccase"
            7 => ["type","height","diameter","width","slots"],
            #"coolers"
            8 => ["cooling_type","cooling","height","vents","width","consumption"],
            #"hdd"
            9 => ["series","interface","size","reading_speed","buffer_size"],
        ];
        $optionValues=[
            "interface"=>["TEST","EVGA","AMD","ARM","AM64"],
            "series"=>[1000,2000,3000,4000,5000,6000,7000,8000,9000],
            "clock"=>[random_int(300,1800),random_int(300,1800),random_int(300,1800),random_int(300,1800),random_int(300,1800)],
            "memory_type"=>["DDR3","DDR4","DDR5","DDR6"],
            "memory_size"=>[4,8,12,16,24,32,48,64,128],
            "consumption"=>[50,75,100,150,200,250,300,350],
            "socket"=>["BigSock","AM64","TEST","ARM"],
            "core"=>["Coffee Lake","AMD"],
            "core_number"=>[6,8,12,16,24],
            "frequency"=>[2.5,3,3.5,3.8,4,4.2,4.3,4.5,5,5.5,5.6,6],
            "mem_frequency"=>[2400,2800,3200,3600,4000,4200,4400,4600,4800],
            "latency"=>[15,17,18,22,25,30,35,40],
            "format"=>["ATX","mATX","mini-ITX"],
            "chipset_producer"=>["Asus","Gigabyte","EVGA","Intel"],
            "chipset_model"=>["Z630","H340","Z320","H220","Z490"],
            "tech"=>["Dual Channel"],
            "size"=>[500,600,1000,1500,2000,2500],
            "max_reading"=>[300,350,400,450,500],
            "power"=>[450,500,600,750,800,900,1000],
            "vent"=>[1],
            "pfc"=>[1,0],
            "efficiency"=>["90%","80%","100%"],
            "certification"=>["Gold","Silver","Bronze"],
            "type"=>["Full-Tower","Mid-Tower","Mini-Tower"],
            "height"=>[50,100,150,200,250,300],
            "width"=>[50,100,150,200,250,300],
            "diameter"=>[50,100,150,200,250,300],
            "slots"=>[8,10,15,20],
            "cooling_type"=>["Air","Liquid"],
            "cooling"=>[1,0],
            "vents"=>[1,2,3],
            "reading_speed"=>[45,50,60,70,80,100,150,200],
            "buffer_size"=>[15,30,50,80,100,150],
        ];
        $products=array();
        $locations=$this->entityManager->getRepository(Locations::class)->findAll();

        // GET LAST ID WHICH WILL BE USED TO NAME THE PRODUCT
        $latestId = $this->entityManager->getRepository(Product::class)->findOneBy(array(),array('id'=>'DESC'),1,0)->getId();

        for($i=0;$i<100;$i++){
            $product=new Product();
            $category_id=random_int(1,9);
            try {
                $category = $this->entityManager->getReference(Category::class, $category_id);
            } catch (ORMException $e) {
                return $product;
            }
            $this->setProductProperties($product,$category,$latestId++);
            foreach($optionArray[$category_id] as $option){
                $optionValCount=count($optionValues[$option]);
                $optionObj=new Option();
                $optionObj->setProduct($product);
                $optionObj->setOptionName($option);
                $optionObj->setOptionValue($optionValues[$option][random_int(0,$optionValCount-1)]);
                $product->addOption($optionObj);
                $this->entityManager->persist($optionObj);
            }
            foreach($locations as $location){
                $prodInvObj=new ProductInventory();
                $prodInvObj->setQuantity(10);
                $prodInvObj->setCreatedAt(new \DateTimeImmutable());
                $prodInvObj->setModifiedAt(new \DateTimeImmutable());
                $prodInvObj->setLocation($location);
                $product->addProductInventory($prodInvObj);
                $this->entityManager->persist($prodInvObj);
            }
            $uuid = Uuid::v4();
            $product->setUid($uuid);
            $product->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($product);
            $products[]=$product;
        }
        $this->entityManager->flush();
        return $products;
    }

    #[Route('/pcbuilder/calc_build/{income}', name: 'app_pcbuilder_test')]
    public function testCalcBuild(mixed $income){
        set_time_limit(9000);
//        for($i=0;$i<5;$i++)
//            $this->build100Products();
//        dd("done");
        $products = $this->entityManager->getRepository(Product::class)->findBy(["status" => 1]);
        $num_prods = count($products);
        for($j=0;$j<10;$j++) {
            $duration_multiple=array();
            $duration_single=array();
//            $mem_usage_multiple=array();
//            $mem_usage_single=array();
            for ($i = 0; $i < 100; $i++) {
                $stopwatch = new Stopwatch();
                $buildTemplate = new PCBuilderTemplate();
                $stopwatch->start('PCBuilderOneBuild');
                $result1 = $this->calculateBuildForNow($income, $buildTemplate);
                $event_time = $stopwatch->stop('PCBuilderOneBuild');
                $stopwatch->start('PCBuilderMoreBuilds');
                $result2 = $this->calculateBuildForNow($income);
                $event_time2 = $stopwatch->stop('PCBuilderMoreBuilds');
                $duration_multiple[] = $event_time2->getDuration();
                $duration_single[] = $event_time->getDuration();
//                $mem_usage_multiple[] = $event_time2->getMemory();
//                $mem_usage_single[] = $event_time->getMemory();
//                sleep(3);
            }
            $average_duration_single[] = $this->calculate_average($duration_single);
            $average_duration_multiple[] = $this->calculate_average($duration_multiple);
//            $average_mem_usage_single[] = $this->calculate_average($mem_usage_single);
//            $average_mem_usage_multiple[] = $this->calculate_average($mem_usage_multiple);
        }
        file_put_contents("duration_multiple-".$num_prods.".json", json_encode($average_duration_multiple));
        file_put_contents("duration_single-".$num_prods.".json", json_encode($average_duration_single));
//        file_put_contents("mem_usage_multiple-".$num_prods.".json", json_encode($average_mem_usage_multiple));
//        file_put_contents("mem_usage_single-".$num_prods.".json", json_encode($average_mem_usage_single));
        dd($average_duration_multiple,$average_duration_single);
        //return new Response("Finished with average single duration ".$average_duration_single." ms with ".$average_mem_usage_single." MiB, average multiple duration ".$average_duration_multiple." ms with ".$average_mem_usage_multiple." MiB.");
    }
    private function calculate_average($arr){
        $sum=0;
        foreach($arr as $elem){
            $sum+=$elem;
        }
        $sum=$sum/count($arr);
        return $sum;
    }
    public function calculateBuildForNow(mixed $income=2000, &$buildTemplate=null)
    {
        // PERCENTAGES FOR GPU, CPU, RAM, STORAGE, CASE in that order
        $categories=[
//            4,
            1,2,3,9,5,7,
//            "motherboard",
//"gpu","cpu","memory","hdd","ssd","pccase",
        ];
        // SEPARATE PERCENTAGES FOR MOTHERBOARDS FOR EACH BUILD, USED AFTER PICKING THE MAIN COMPONENTS
        $build_arr=[];
        $selected_part_arr=array();
        $budget_extra=0;
        // Items are added to a blacklist when found once, so the 4 builds you get are all different from eachother
        $blacklisted_items=array();
        foreach($categories as $cat){
            $blacklisted_items[$cat]=array();
        }
        if($buildTemplate!=null){
            $builds=[
                [0.35,0.20,0.08,0.10,0.10,0.06],
            ];
            $mb_percentages=
                [0.06];
            $existing_parts_counter=0;
            foreach($buildTemplate->getCartItems() as $ci){
                $product=$ci->getProduct();
                $selected_part_arr[$product->getCategory()->getId()]=$product;
                $existing_parts_counter+=$ci->getQuantity();
            }
            $remaining_parts=(count($categories)-$existing_parts_counter+1);
            // WE GET THE REMAINING INCOME AND SPLIT IT WITH THE REMAINING CATEGORIES THAT AREN'T SELECTED TO SEE HOW MUCH WE NEED TO ADD/REMOVE FOR EACH ONE
            if($remaining_parts>0)
                $budget_extra=($income-$buildTemplate->getTotal())/$remaining_parts;
        }else{
            $builds=[
                [0.35,0.20,0.08,0.10,0.10,0.06],
                [0.30,0.20,0.08,0.10,0.10,0.06],
                [0.20,0.40,0.08,0.10,0.10,0.06],
                [0.40,0.28,0.08,0.06,0.06,0.06],
            ];
            $mb_percentages=
                [0.06,0.06,0.06,0.06];
        }

        foreach($builds as $build_key=>$build_percentages){
            $build_arr[$build_key]=$selected_part_arr;
            $money_per_parts=array();
            foreach($build_percentages as $percentage)
                $money_per_parts[]=$percentage*$income+$budget_extra;
            $found_mb=false;
            if(key_exists(4,$build_arr[$build_key])){
                if(!key_exists(3,$build_arr[$build_key])) {
                    $mb_ram_common_filter = $this->getFilter("memory_type", $build_arr[$build_key][4]);
                    $ram_parts = $this->entityManager->getRepository(Product::class)->findItemsInCategoryUnderPrice(3, $money_per_parts[2], [$mb_ram_common_filter->getOptionName() => $mb_ram_common_filter->getOptionValue()]);
                    $this->removeBlacklistedItems($ram_parts,$blacklisted_items[3]);
                    $bestPart=$this->getBestPart($ram_parts);
                    $build_arr[$build_key][3] = $bestPart;
                    array_push($blacklisted_items[3],$bestPart);
                }
                if(!key_exists(2,$build_arr[$build_key])) {
                    $mb_cpu_common_filter = $this->getFilter("socket", $build_arr[$build_key][4]);
                    $cpu_parts = $this->entityManager->getRepository(Product::class)->findItemsInCategoryUnderPrice(2, $money_per_parts[1], [$mb_cpu_common_filter->getOptionName() => $mb_cpu_common_filter->getOptionValue()]);
                    $this->removeBlacklistedItems($cpu_parts,$blacklisted_items[2]);
                    $bestPart = $this->getBestPart($cpu_parts);
                    $build_arr[$build_key][2] = $bestPart;
                    array_push($blacklisted_items[2],$bestPart);
                }
                unset($money_per_parts[1],$money_per_parts[2]);
                $found_mb=true;
            }
            foreach ($money_per_parts as $key => $part_income) {
                if (!key_exists($categories[$key], $build_arr[$build_key])) {
                    $parts = $this->entityManager->getRepository(Product::class)->findItemsInCategoryUnderPrice($categories[$key], $part_income);
                    $this->removeBlacklistedItems($parts,$blacklisted_items[$categories[$key]]);
                    $bestPart = $this->getBestPart($parts);
                    $build_arr[$build_key][$categories[$key]] = $bestPart;
                    array_push($blacklisted_items[$categories[$key]],$bestPart);
                }
            }

            if(!$found_mb){
                $mb_filters = [];
                if (key_exists(2, $build_arr[$build_key]) && $build_arr[$build_key][2] != null) {
                    $mb_cpu_common_filter = $this->getFilter("socket", $build_arr[$build_key][2]);
                    $mb_filters[$mb_cpu_common_filter->getOptionName()] = $mb_cpu_common_filter->getOptionValue();
                }
                if (key_exists(3, $build_arr[$build_key]) && $build_arr[$build_key][3] != null) {
                    $mb_ram_common_filter = $this->getFilter("memory_type", $build_arr[$build_key][3]);
                    $mb_filters[$mb_ram_common_filter->getOptionName()] = $mb_ram_common_filter->getOptionValue();
                }
                $parts = $this->entityManager->getRepository(Product::class)->findItemsInCategoryUnderPrice(4, $mb_percentages[$build_key] * $income + $budget_extra, $mb_filters);

                if (count($parts) > 0) {
                    if(key_exists(4,$blacklisted_items))
                        $this->removeBlacklistedItems($parts,$blacklisted_items[4]);
                    $bestPart=$this->getBestPart($parts);
                    $build_arr[$build_key][4] = $bestPart;
                    if(!key_exists(4,$blacklisted_items))
                        $blacklisted_items[4][]=$bestPart;
                    else
                        array_push($blacklisted_items[4],$bestPart);
                }
            }
        }
        return $build_arr;
    }

    private function removeBlacklistedItems(&$collection,$blacklisted_items){
        if(count($collection)>1){
            foreach($blacklisted_items as $bi){
                $key=array_search($bi,$collection);
                if ($key){
                    unset($collection[$key]);
                }
            }
        }
    }
    private function getFilter($filter_name,Product $product){
        foreach($product->getOptions() as $option){
            if($option->getOptionName()==$filter_name)
                return $option;
        }
    }
    private function getBestPart($parts){
        $bestPart=null;
        $maxScore=PHP_INT_MIN;
        foreach($parts as $part){
            // Parts are ordered descending based on price so if we want the best and cheapest part we do >= if we find a similar score one after which can only mean is cheaper
            $partScore=$part->getScore();
            if($partScore>=$maxScore){
                $bestPart=$part;
                $maxScore=$partScore;
            }
        }
        return $bestPart;
    }
    private function getSelectedProducts($builderTemplate){
        $selectedProducts=array();
        foreach($builderTemplate->getCartItems() as $cartItem){
            $product=$cartItem->getProduct();
            $selectedProducts[$product->getCategory()->getCategoryName()]=$cartItem;
        }
        return $selectedProducts;
    }
    private function getPSUAdvice($builderTemplate,$consumption_total=null){
        if($consumption_total=null){
            $consumption_total=$builderTemplate->getConsumptionTotal();
        }
        $psu=$builderTemplate->getPSU();
        if($psu!=null){
            $psu=$psu->getProduct();
            $psuPower=$this->getPSUPowerOption($psu)->getOptionValue();
            $psuAdvice=$this->PSUAdvise($psuPower,$consumption_total);
        }else{
            $psuAdvice=null;
        }
        return $psuAdvice;
    }
    private function getCompatibilityAdvice(PCBuilderTemplate $builderTemplate){
        $cpu_id=2;
        $memory_id=3;
        $motherboard_id=4;
        $categoriesToCheck = [
          $cpu_id=>null,
          $memory_id=>null,
          $motherboard_id=>null,
        ];
        foreach($builderTemplate->getCartItems() as $ci){
            $product=$ci->getProduct();
            $category_id=$product->getCategory()->getId();
            if(array_key_exists($category_id,$categoriesToCheck)){
               $categoriesToCheck[$category_id]=$product;
            }
        }
        $problems=array();
        if($categoriesToCheck[$motherboard_id]!=null){
            $mb_options=['memory_type','socket'];
            $problem_description="Incompatbile with motherboard";
            $motherboardCompatibilities=$categoriesToCheck[$motherboard_id]->findOptions($mb_options);
            $mb_problem_description="Incompatibility with one or more components detected.";
            // Check memory compatibility
            $mem_compat_problem=false;
            if($categoriesToCheck[$memory_id]!=null){
                $compatibility_option=['memory_type'];
                $problem_cause=' by memory type comparison.';
                $memoryCompatibilities=$categoriesToCheck[$memory_id]->findOptions($compatibility_option);
                //Check memory_type
                if($memoryCompatibilities[$compatibility_option[0]]->getOptionValue()!=$motherboardCompatibilities[$compatibility_option[0]]->getOptionValue()){
                    $problems[$memory_id]=$problem_description.$problem_cause;
                    $mem_compat_problem=true;
                }
            }
            // Check cpu compatibility
            $cpu_compat_problem=false;
            if($categoriesToCheck[$cpu_id]!=null){
                $compatibility_option=['socket'];
                $problem_cause=' by socket comparison.';
                $memoryCompatibilities=$categoriesToCheck[$cpu_id]->findOptions($compatibility_option);
                //Check memory_type
                if($memoryCompatibilities[$compatibility_option[0]]->getOptionValue()!=$motherboardCompatibilities[$compatibility_option[0]]->getOptionValue()){
                    $problems[$cpu_id]=$problem_description.$problem_cause;
                    $cpu_compat_problem=true;
                }
            }
            if($mem_compat_problem || $cpu_compat_problem){
                $problems[$motherboard_id]=$mb_problem_description;
            };

        }
        return $problems;
    }
}
