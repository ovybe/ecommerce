<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class OrderPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class,[
                'choices' => [
                    'Delivery' => "delivery",
                    'Reservation' => "reservation",
                ],
                'constraints' => [
                    // TODO: add validation for order type
                ],
            ])
            ->add('paymentType', ChoiceType::class,[
                'choices' => [
                    'Online' => "online",
                    'Cash' => "cash",
                ],
                'constraints' => [
                    // TODO: add validation
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
