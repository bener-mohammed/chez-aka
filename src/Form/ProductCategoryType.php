<?php

namespace App\Form;

use App\Entity\ProductCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCategoryType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
            ])

            ->add('displayOrder', IntegerType::class, [
                'label' => 'Ordre d’affichage',
                'attr' => [
                    'min' => 0,
                ],
                'help' => 'Plus le nombre est petit, plus la catégorie apparaît tôt.',
            ])

            ->add('isActive', CheckboxType::class, [
                'label' => 'Catégorie active',
                'required' => false,
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => ProductCategory::class,
        ]);
    }
}