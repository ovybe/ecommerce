<?php

namespace App\Form;

use App\Entity\Memory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('memtype')
            ->add('capacity',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
//                'constraints' => [
//                    new NotNull(),
//                    new Positive(),
//                ],
            ])
            ->add('frequency',null,[
                'attr' => ['class'=>'rounded col-12 rq'],
//                'constraints' => [
//                    new NotNull(),
//                    new Positive(),
//                ],
            ])
            ->add('latency')
        //    ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Memory::class,
        ]);
    }
}
