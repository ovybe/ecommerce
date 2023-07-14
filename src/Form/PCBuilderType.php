<?php

namespace App\Form;

use App\Entity\PCBuilderTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PCBuilderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('templateName',TextType::class,['attr'=>['class'=>'d-none']])
            ->add('templateDescription', HiddenType::class)
//            ->add('products')
//            ->add('owningUser')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PCBuilderTemplate::class,
        ]);
    }
}
