<?php

namespace App\Form;

use App\Entity\Countries;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('country', EntityType::class,[
                'class' => Countries::class,
                'choice_label' => 'name'
            ])
//            ->add('plainPassword', PasswordType::class, [
//                // instead of being set onto the object directly,
//                // this is read and encoded in the controller
//                'mapped' => false,
//                'attr' => ['autocomplete' => 'new-password'],
//                'constraints' => [
//                    new NotBlank([
//                        'message' => 'Please enter a password',
//                    ]),
//                    new Length([
//                        'min' => 6,
//                        'minMessage' => 'Your password should be at least {{ limit }} characters',
//                        // max length allowed by Symfony for security reasons
//                        'max' => 4096,
//                    ]),
//                ],
//            ])
//            ->add('contacts',CollectionType::class,[
//                // each entry in the array will be an "email" field
//                'entry_type' => ContactType::class,
//                'allow_add' => true,
//                'prototype' => true,
//                // these options are passed to each "email" type
//                'entry_options' => [
//                    'attr' => ['class' => 'contact-box'],
//                ],
//                'attr' => ['id'=>'contactdiv'],
//            ])
            ->add('submit', SubmitType::class, ['label' => '<i class="bi bi-pencil-square"></i>', 'attr'=> ['class'=>'btn btn-outline-dark']])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'required' => false,
        ]);
    }
}
