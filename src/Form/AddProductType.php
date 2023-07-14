<?php

namespace App\Form;

use App\Entity\Motherboard;
use App\Entity\Product;
use App\Entity\ProductInventory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product',ProductType::class)
            ->add('type',ChoiceType::class,[
                'choices' => [
                    'Alege categoria produsului' => [
                        'GPU' => 1,
                        'CPU' => 2,
                        'Memory' => 3,
                        'Motherboard' => 4,
                        'SSD' => 5,
                        'PSU' => 6,
                        'PC Case' => 7,
                        'Cooler' => 8,
                        'Hard Disk' => 9,
                    ],

                ]
            ])
            ->add('embeddedForm',FormType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSubmit'])
            //            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSubmit'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'required' => false,
            'allow_extra_fields' => true,
//            'data_class' => Product::class,
        ]);
    }
    public function onSubmit(FormEvent $event): void
    {
        $product = $event->getData();
        $form = $event->getForm();
        //dd($product);
        switch ($product['type']) {
            case 1:
                $form->add('embeddedForm', GpuType::class);
                break;
            case 2:
                $form->add('embeddedForm', CpuType::class);
                break;
            case 3:
                $form->add('embeddedForm', MemoryType::class);
                break;
            case 4:
                $form->add('embeddedForm', MotherboardType::class);
                break;
            case 5:
                $form->add('embeddedForm', SsdType::class);
                break;
            case 6:
                $form->add('embeddedForm', PsuType::class);
                break;
            case 7:
                $form->add('embeddedForm', PccaseType::class);
                break;
            case 8:
                $form->add('embeddedForm', CoolerType::class);
                break;
            case 9:
                $form->add('embeddedForm', HddType::class);
                break;
        }
    }
}
