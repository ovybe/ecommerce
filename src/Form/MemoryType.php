<?php

namespace App\Form;

use App\Entity\Memory;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class MemoryType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('memory_type')
            ->add('memory_size',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
//                'constraints' => [
//                    new NotNull(),
//                    new Positive(),
//                ],
            ])
            ->add('mem_frequency',NumberType::class,[
                'attr' => ['class'=>'rounded col-12 rq'],
//                'constraints' => [
//                    new NotNull(),
//                    new Positive(),
//                ],
            ])
            ->add('latency',NumberType::class)
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
