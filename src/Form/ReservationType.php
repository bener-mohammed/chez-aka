<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\ServiceDay;
use App\Repository\ServiceDayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('serviceDay', EntityType::class, [
                'class' => ServiceDay::class,
                'label' => 'Date de réservation',

                'query_builder' => function (
                    ServiceDayRepository $repository
                ) {
                    return $repository
                        ->createQueryBuilder('sd')
                        ->andWhere('sd.isOpen = :open')
                        ->andWhere('sd.serviceDate >= :today')
                        ->setParameter('open', true)
                        ->setParameter(
                            'today',
                            new \DateTimeImmutable('today')
                        )
                        ->orderBy('sd.serviceDate', 'ASC');
                },

                'choice_label' => function (
                    ServiceDay $serviceDay
                ): string {
                    return $serviceDay
                        ->getServiceDate()
                        ?->format('d/m/Y') ?? '';
                },

                'placeholder' => 'Choisissez une date',
            ])

            ->add('reservationTime', TimeType::class, [
                'label' => 'Heure de réservation',
                'widget' => 'choice',
                'input' => 'datetime_immutable',
                'hours' => [20, 21],
                'minutes' => [0, 30],
            ])

            ->add('partySize', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'attr' => [
                    'min' => 1,
                    'max' => 12,
                ],
            ])

            ->add('areaPreference', ChoiceType::class, [
                'label' => 'Préférence de placement',
                'choices' => [
                    'Sans préférence' => 'NO_PREFERENCE',
                    'Intérieur' => 'INDOOR',
                    'Terrasse' => 'TERRACE',
                ],
            ])

            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'autocomplete' => 'given-name',
                ],
            ])

            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'autocomplete' => 'family-name',
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])

            ->add('phoneNumber', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'autocomplete' => 'tel',
                ],
            ])

            ->add('specialRequest', TextareaType::class, [
                'label' => 'Demande spéciale',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 1000,
                    'placeholder' => 'Allergies, poussette, demande particulière...',
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}