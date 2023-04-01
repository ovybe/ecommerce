<?php

namespace App\Form;

use App\Entity\Psu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PsuType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('power',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
            ])
            ->add('pfc', ChoiceType::class,[
                'choices' => [
                    'Active' => true,
                    'Passive' => false,
                ]
            ])
            ->add('efficiency')
            ->add('certification')
         //   ->add('locations')
             ->add('vents',CollectionType::class,[
                 // each entry in the array will be an "email" field
                 'entry_type' => VentType::class,
                 'allow_add' => true,
                 'prototype' => true,
                 // these options are passed to each "email" type
                 'entry_options' => [
                     'attr' => ['class' => 'vents-box list-group-item'],
                 ],
                 'attr' => ['id'=>'vdiv'],
             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Psu::class,
        ]);
    }
}
