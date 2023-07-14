<?php

namespace App\Form;

use App\Entity\Discount;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceFieldName;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        $datetimeimmutable=new DateTimeImmutable('now',new DateTimeZone($options['timezone']));
//        $datetimeimmutable->setDate(2024,06,06);
//        $years=$datetimeimmutable->format('Y');
//        $months=$datetimeimmutable->format('m');
//        $days=$datetimeimmutable->format('d');
//        $hours=$datetimeimmutable->format('H');
//        $minutes=$datetimeimmutable->format('i');
        $builder
            ->add('code')
            ->add('percentage',CheckboxType::class)
            ->add('price')
            ->add('uses')
            ->add('expiration',TextType::class,[
//                'attr'=>['value'=>$datetimeimmutable]
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSubmit']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Discount::class,
            'timezone' => 'UTC'
        ]);

    }
    public function onSubmit(FormEvent $event): void
    {
        $discount = $event->getData();
//        dd($discount);

        $expDate=$discount['expiration'];
        $datetimeimmutable=new DateTimeImmutable($expDate);
        $discount['expiration']=$datetimeimmutable;

//        dd($datetimeimmutable,$expDate);
//        $product['eventCheck'] .= "1";
        $event->setData($discount);
        }
}
