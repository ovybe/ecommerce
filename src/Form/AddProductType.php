<?php

namespace App\Form;

use App\Entity\Motherboard;
use App\Entity\Product;
use App\Entity\ProductInventory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product',ProductType::class)
            ->add('gpu',GpuType::class)
            ->add('cpu',CpuType::class)
            ->add('motherboard',MotherboardType::class)
            ->add('memory',MemoryType::class)
            ->add('ssd',SsdType::class)
            ->add('psu',PsuType::class)
            ->add('cooler',CoolerType::class)
            ->add('pccase',PCCaseType::class)
            ->add('submit', SubmitType::class, ['label' => 'Add Product', 'attr'=> ['class'=>'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'required' => false,
//            'data_class' => Product::class,
        ]);
    }
}
