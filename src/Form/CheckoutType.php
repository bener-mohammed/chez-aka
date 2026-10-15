<?php

namespace App\Form;

use App\Entity\CustomerOrder;
use App\Entity\ServiceDay;
use App\Repository\ServiceDayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('serviceDay', EntityType::class, [
                'class' => ServiceDay::class,
                'label' => 'Jour de retrait',
                'placeholder' => 'Choisissez un jour',

                'choice_label' => function (
                    ServiceDay $serviceDay
                ): string {
                    return $serviceDay
                        ->getServiceDate()
                        ?->format('d/m/Y') ?? '';
                },

                'query_builder' => function (
                    ServiceDayRepository $repository
                ) {
                    return $repository
                        ->createClickCollectAvailableQueryBuilder();
                },
            ])

            ->add('pickupTime', TimeType::class, [
                'label' => 'Heure de retrait',
                'input' => 'datetime_immutable',
                'widget' => 'choice',
                'hours' => [20, 21, 22],
                'minutes' => [0, 30],
                'with_seconds' => false,
            ])

            ->add('specialRequest', TextareaType::class, [
                'label' => 'Demande spéciale',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 1000,
                ],
                'help' => 'Facultatif - 1000 caractères maximum.',
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => CustomerOrder::class,
        ]);
    }
}