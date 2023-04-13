<?php

namespace App\Form;

use App\Entity\Cooler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CoolerType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('ctype', ChoiceType::class,[
                'choices' => [
                    'Air' => "Air",
                    'Liquid' => "Liquid",
                ]
            ])
            ->add('cooling', ChoiceType::class,[
                'choices' => [
                    'Active' => true,
                    'Passive' => false,
                ]
            ])
            ->add('height')
            ->add('vents')
            ->add('size')
        //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cooler::class,
        ]);
    }
}
