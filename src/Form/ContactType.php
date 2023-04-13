<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\Countries;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('city')
            ->add('country', EntityType::class,[
                'class' => Countries::class,
                'choice_label' => 'name'
            ])
            ->add('county')
            ->add('address')
            ->add('postalCode')
            ->add('phone')
            ->add('email')
            //->add('owner')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
