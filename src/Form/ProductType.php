<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('shortDesc',HiddenType::class)
            ->add('description',HiddenType::class)
            ->add('SKU',null,[
                'label' => 'SKU Code',
                'attr' => ['class'=>'rounded col-5 ms-5'],
                'label_attr' => ['class'=>'col-5 ms-5'],
                ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Thumbnail (Image)',

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image! (PNG/JPEG/JPG)',
                    ])
                ],
            ])
            ->add('productImages',CollectionType::class,[
                // each entry in the array will be an "email" field
                'entry_type' => FileType::class,
                'entry_options'=>[
                    'constraints'=>[
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/jpg'
                            ],
                            'mimeTypesMessage' => 'Please upload a valid image! (PNG/JPEG/JPG)',
                        ])
                   ],
                    'attr' => ['class' => 'image-box'],
                    'label'=>false,
                ],
                'allow_add' => true,
                'prototype' => true,
                'attr' => ['id'=>'imgdiv'],
                'required' => false,
            ])
            ->add('type',ChoiceType::class,[
                'choices' => [
                    'Alege categoria produsului' => [
                        'GPU' => 'Gpu',
                        'CPU' => 'Cpu',
                        'Motherboard' => 'Motherboard',
                        'Memory' => 'Memory',
                        'Storage' => 'Ssd',
                        'PSU' => 'Psu',
                        'PC Case' => 'PCCase',
                        'Cooler' => 'Cooler',
                    ],

                ]
            ])
            ->add('seller')
            ->add('price',null,[
                'attr' => ['class'=>'rounded col-5 ms-0'],
                'label_attr' => ['class'=>'col-5 ms-0'],
                'constraints' => [
                    new Positive()
                ],
            ])
            ->add('status', ChoiceType::class,[
                'choices' => [
                    'Draft' => 0,
                    'In Stock' => 1,
                    'Out of Stock' => 2,
                    ],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 2,
                        'notInRangeMessage' => 'You must be between {{ min }} and {{ max }} for status to work.',
                    ])
                ],
            ])
            ->add('productInventories',CollectionType::class,[
                // each entry in the array will be an "email" field
                'entry_type' => ProductInventoryType::class,
                'allow_add' => true,
                'prototype' => true,
                // these options are passed to each "email" type
                'entry_options' => [
                    'attr' => ['class' => 'inventory-box'],
                    ],
                    'attr' => ['id'=>'locdiv'],
                    ])
//            ->add('locations')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'inherit_data' => true,
            'productInventories'=>null,
        ]);
    }
}
