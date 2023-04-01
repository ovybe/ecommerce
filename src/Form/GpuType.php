<?php

namespace App\Form;

use App\Entity\Gpu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class GpuType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //parent::buildForm($builder, $options);
        $builder
            ->add('interface',null, [
                'attr' => ['class'=>'rounded col-12 rq'],
//                'constraints' => [
//                    new NotBlank()
//                ]
            ])
            ->add('clock')
            ->add('memory')
            ->add('size')
            ->add('releasedate',DateType::class,[
                'widget' => 'single_text',

                // prevents rendering it as type="date", to avoid HTML5 date pickers
                'html5' => false,

                // adds a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('series')
            //->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

//            'inherit_data' => true,
            'data_class' => Gpu::class,
            'attr' => ['class'=>'rounded col-12'],
            'label_attr' => ['class'=>'col-12 ms-5'],
        ]);
    }
}
