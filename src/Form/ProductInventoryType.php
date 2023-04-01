<?php

namespace App\Form;

use App\Entity\ProductInventory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductInventoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity',null,[
                'label' => false,
                'attr' => ['class' => 'col-12 rounded'],
            ])
//            ->add('createdAt')
//            ->add('modifiedAt')
//            ->add('product')
//            ->add('location')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductInventory::class,
        ]);
    }
}
