<?php

namespace App\Form;

use App\Entity\Cpu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CpuType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('socket',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
            ])
            ->add('series')
            ->add('core')
            ->add('frequency')
        //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cpu::class,
        ]);
    }
}
