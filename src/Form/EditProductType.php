<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProductType extends AbstractType
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
            ->add('oldType',HiddenType::class)
            ->add('eventCheck',HiddenType::class)
            #->add('embeddedForm',FormType::class)
            ->add('embeddedForm',$options['form_type'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSubmit'])

//            ->add('gpu',GpuType::class)
//            ->add('cpu',CpuType::class)
//            ->add('motherboard',MotherboardType::class)
//            ->add('memory',MemoryType::class)
//            ->add('ssd',SsdType::class)
//            ->add('hdd',HddType::class)
//            ->add('psu',PsuType::class)
//            ->add('cooler',CoolerType::class)
//            ->add('pccase',PCCaseType::class)
//            ->add('submit', SubmitType::class, ['label' => 'Add Product', 'attr'=> ['class'=>'btn btn-outline-dark']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'required' => false,
            'allow_extra_fields' => true,
            'form_type' => FormType::class,
//            'data_class' => Product::class,
        ]);
    }
    public function onSubmit(FormEvent $event): void
    {
        $product = $event->getData();
        #dd($product);
        $product['eventCheck'] .= "1";
        $event->setData($product);
        #dd($product);
        $form = $event->getForm();
        if($product['type']!=$product['oldType']){
            $product['oldType']=$product['type'];
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
//        $embeddedForm=$form->get('embeddedForm');
//        if($oldEmbeddedForm->getConfig()->getType()->getInnerType()==$embeddedForm->getConfig()->getType()->getInnerType())
//            $embeddedForm->setData($oldEmbeddedForm->getData());
    }
}
