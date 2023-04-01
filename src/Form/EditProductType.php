<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
           // $type=("App\\Form\\".$options['type']."Type");

        $builder
            ->add('product',ProductType::class)
            ->add('gpu',GpuType::class)
            ->add('cpu',CpuType::class)
            ->add('motherboard',MotherboardType::class)
            ->add('memory',MemoryType::class)
            ->add('ssd',SsdType::class)
            ->add('psu',PsuType::class)
            ->add('cooler',CoolerType::class)
            ->add('case',PCCaseType::class)
            ->add('submit', SubmitType::class, ['label' => 'Edit Product', 'attr'=> ['class'=>'btn btn-outline-dark']]);
        //dd($type);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
//        $resolver->setRequired(['type']);
        $resolver->setDefaults([
            // Configure your form options here
            'required' => false,
            //'data_class' =>Product::class,
        ]);
    }
}
