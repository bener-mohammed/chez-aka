<?php

namespace App\Form;

use App\Entity\RestaurantEvent;
use App\Entity\ServiceDay;
use App\Repository\ServiceDayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantEventType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        /** @var RestaurantEvent|null $currentEvent */
        $currentEvent = $options['data'] ?? null;

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l’événement',
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'maxlength' => 3000,
                ],
            ])

            ->add('serviceDay', EntityType::class, [
                'class' => ServiceDay::class,
                'label' => 'Jour de service',
                'placeholder' => 'Choisissez un jour de service',

                'choice_label' => static function (
                    ServiceDay $serviceDay
                ): string {
                    $date = $serviceDay->getServiceDate();

                    return $date !== null
                        ? $date->format('d/m/Y')
                        : '';
                },

                'query_builder' => static function (
                    ServiceDayRepository $repository
                ) use ($currentEvent) {
                    $qb = $repository
                        ->createQueryBuilder('s')
                        ->leftJoin('s.restaurantEvent', 'e')
                        ->orderBy('s.serviceDate', 'ASC');

                    if (
                        $currentEvent instanceof RestaurantEvent
                        && $currentEvent->getId() !== null
                    ) {
                        $qb
                            ->andWhere(
                                'e.id IS NULL OR e.id = :currentEventId'
                            )
                            ->setParameter(
                                'currentEventId',
                                $currentEvent->getId()
                            );
                    } else {
                        $qb->andWhere('e.id IS NULL');
                    }

                    return $qb;
                },
            ])

            ->add('startDatetime', DateTimeType::class, [
                'label' => 'Début de l’événement',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
            ])

            ->add('endDatetime', DateTimeType::class, [
                'label' => 'Fin de l’événement',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'required' => false,
            ])

            ->add('imagePath', TextType::class, [
                'label' => 'Chemin de l’image',
                'required' => false,
                'help' => 'Exemple : /images/events/concert-octobre.jpg',
            ])

            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier l’événement',
                'required' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => RestaurantEvent::class,
        ]);
    }
}