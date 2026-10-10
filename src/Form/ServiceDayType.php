<?php

namespace App\Form;

use App\Entity\ServiceDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceDayType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('serviceDate', DateType::class, [
                'label' => 'Date du service',
                'widget' => 'single_text',
            ])

            ->add('isOpen', CheckboxType::class, [
                'label' => 'Restaurant ouvert',
                'required' => false,
            ])

            ->add('indoorCapacity', IntegerType::class, [
                'label' => 'Capacité intérieure',
            ])

            ->add('terraceCapacity', IntegerType::class, [
                'label' => 'Capacité terrasse',
            ])

            ->add('maxOnlinePartySize', IntegerType::class, [
                'label' => 'Nombre maximum de personnes par réservation en ligne',
            ])

            ->add('clickCollectEnabled', CheckboxType::class, [
                'label' => 'Click & Collect activé',
                'required' => false,
            ])

            ->add('clickCollectOrderLimit', IntegerType::class, [
                'label' => 'Nombre maximum de commandes Click & Collect',
                'required' => false,
                'help' => 'Laisser vide s’il n’y a pas de limite.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceDay::class,
        ]);
    }
}