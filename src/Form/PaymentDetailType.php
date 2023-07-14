<?php

namespace App\Form;

use App\Entity\PaymentDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('cardNumbers')
            ->add('cardHolder')
            ->add('expirationDate')
//            ->add('createdAt')
//            ->add('modifiedAt')
//            ->add('owner')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentDetail::class,
        ]);
    }
}
