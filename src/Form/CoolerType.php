<?php

namespace App\Form;

use App\Entity\Cooler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CoolerType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('cooling_type', ChoiceType::class,[
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
            ->add('height',NumberType::class)
            ->add('vents',NumberType::class)
            ->add('width',NumberType::class)
            ->add('consumption',NumberType::class)
            ->add('submit', SubmitType::class, ['label' => 'Add Product', 'attr'=> ['class'=>'btn btn-outline-dark']])

            //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }
}
