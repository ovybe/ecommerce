<?php

namespace App\Form;

use App\Entity\Cpu;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class CpuType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('socket',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
                'constraints' => [new NotBlank()],
            ])
            ->add('series')
            ->add('core')
            ->add('core_number',NumberType::class)
            ->add('frequency',NumberType::class)
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
