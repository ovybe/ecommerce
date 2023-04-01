<?php

namespace App\Form;

use App\Entity\Motherboard;
use Symfony\Component\Form\AbstractType;
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
            ->add('cpusocket',null,[
//                'constraints' => [
//                    new NotBlank()
//                ]
            ])
            ->add('chipset')
            ->add('modelchipset')
            ->add('interface')
            ->add('memory')
            ->add('tech')
         //   ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Motherboard::class,
            'validation_groups' => function(FormInterface $form) {
                // If the form is disabled, don't use any constraints
                $form_data=$form->getParent()->getData();
                if ($form_data['type']==ucfirst($this->getBlockPrefix())) {
                    return 'need_validation';
                }

                // Otherwise, use the default validation group
                return 'Default';
            }
        ]);
    }
}
