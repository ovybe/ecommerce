<?php

namespace App\Form;

use App\Entity\Cities;
use App\Entity\Countries;
use App\Entity\Order;
use App\Form\DataTransformer\CitiesToStringTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function __construct(
        private CitiesToStringTransformer $citiesToStringTransformer,
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', EntityType::class,[
                'class' => Countries::class,
                'choice_label' => 'name',
                'choice_attr' => ChoiceList::attr($this, function (?Countries $country) {
                    return $country ? ['data-phone' => $country->getPhonecode()] : [];
                }),
            ])
            ->add('city',TextType::class)
            ->add('addressLine1')
            ->add('addressLine2')
            ->add('postalCode')
            ->add('phone')
            ->add('customerName')
        ;
//        $builder->get('city')
//            ->addModelTransformer($this->citiesToStringTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
//            'data_class' => Order::class,
              'attr'
        ]);
    }
}
