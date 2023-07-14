<?php

namespace App\Form;

use App\Entity\Motherboard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MotherboardType extends ProductType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        parent::buildForm($builder, $options);
        $builder
            ->add('format')
            ->add('socket',null,[
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('chipset_producer')
            ->add('chipset_model')
            ->add('interface')
            ->add('memory_type')
            ->add('tech')
            ->add('consumption',NumberType::class)
            ->add('submit', SubmitType::class, ['label' => 'Add Product', 'attr'=> ['class'=>'btn btn-outline-dark']])

            //   ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
//            'validation_groups' => function(FormInterface $form) {
//                // If the form is disabled, don't use any constraints
//                $form_data=$form->getParent()->getData();
//                #dd($form_data);
//                if ($form_data['type']==4) {
//                    return 'need_validation';
//                }
//
//                // Otherwise, use the default validation group
//                return 'Default';
//            }
        ]);
    }
}
