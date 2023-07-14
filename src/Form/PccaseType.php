<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PccaseType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('type',ChoiceType::class,[
                'choices' => [
                    'Full-Tower' => "Full-Tower",
                    'Mid-Tower' => "Mid-Tower",
                    "Mini-Tower" => "Mini-Tower",
                    "SFF" => "SFF",
                ]
            ])
            ->add('height',NumberType::class)
            ->add('diameter',NumberType::class)
            ->add('width',NumberType::class)
            ->add('slots',NumberType::class)
            ->add('submit', SubmitType::class, ['label' => 'Add Product', 'attr'=> ['class'=>'btn btn-outline-dark']])

            //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class'=>'rounded col-12 rq'],
            'allow_extra_fields' => true,
        ]);
    }
}
