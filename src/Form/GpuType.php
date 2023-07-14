<?php

namespace App\Form;

use App\Entity\Gpu;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
//                'attr' => ['class'=>'rounded col-12 rq'],
//                'constraints' => [
//                    new NotBlank()
//                ]
            ])
            ->add('clock',NumberType::class)
            ->add('memory_type')
            ->add('memory_size',NumberType::class)
            ->add('consumption',NumberType::class)
            ->add('series')
            ->add('submit', SubmitType::class, ['label' => 'Add Product', 'attr'=> ['class'=>'btn btn-outline-dark']])
            //->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

//            'inherit_data' => true,
            'attr' => ['class'=>'rounded col-12'],
            'label_attr' => ['class'=>'col-12 ms-5'],
            'allow_extra_fields' => true,
        ]);
    }
}
