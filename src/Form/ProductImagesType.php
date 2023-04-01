<?php

namespace App\Form;

use App\Entity\ProductImages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('path',FileType::class, [
        //'label' => 'Thumbnail (Image)',

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
        ]
            ])
//            ->add('product')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductImages::class,
        ]);
    }
}
