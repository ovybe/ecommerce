<?php

namespace App\Form;

use App\Entity\PCCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PCCaseType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('casetype')
            ->add('height')
            ->add('diameter')
            ->add('width')
            ->add('slots')
        //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PCCase::class,
            'attr' => ['class'=>'rounded col-12 rq'],
        ]);
    }
}
