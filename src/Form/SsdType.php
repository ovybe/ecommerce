<?php

namespace App\Form;

use App\Entity\Ssd;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SsdType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('series')
            ->add('interface')
            ->add('capacity',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
            ])
            ->add('maxreading')
            ->add('buffer')
            ->add('drivetype', ChoiceType::class,[
                'choices' => [
                    'SSD' => "SSD",
                    'HDD' => "HDD",
                ]
            ])
        //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ssd::class,
        ]);
    }
}
