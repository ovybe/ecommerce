<?php

namespace App\Form;

use App\Entity\Countries;
use App\Entity\Locations;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('address')
//            ->add('quantity')
            ->add('city',null,[
                'label' => 'City',
//                'attr' => ['class'=>'rounded col-5 ms-5'],
//                'label_attr' => ['class'=>'col-5 ms-5'],
            ])
            ->add('country', EntityType::class,[
                'class' => Countries::class,
                'choice_label' => 'name'
            ])
//            ->add('country',null,[
//                'label' => 'Country',
////                'attr' => ['class'=>'rounded col-5 ms-5'],
////                'label_attr' => ['class'=>'col-5 ms-5'],
//            ])
            ->add('coordX')
            ->add('coordY')
            ->add('submit', SubmitType::class, ['label' => 'Add Location', 'attr'=> ['class'=>'btn btn-outline-dark']])
//            ->add('products')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Locations::class,
        ]);
    }
}
