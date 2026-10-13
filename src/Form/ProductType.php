<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])

            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'scale' => 2,
                'input' => 'string',
            ])

            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                'label' => 'Catégorie',
                'choice_label' => 'name',
                'placeholder' => 'Choisissez une catégorie',

                'query_builder' => function (
                    ProductCategoryRepository $repository
                ) {
                    return $repository
                        ->createQueryBuilder('c')
                        ->orderBy('c.displayOrder', 'ASC')
                        ->addOrderBy('c.name', 'ASC');
                },
            ])

            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Produit disponible',
                'required' => false,
            ])

            ->add('isClickCollect', CheckboxType::class, [
                'label' => 'Disponible en Click & Collect',
                'required' => false,
            ])

            ->add('displayOrder', IntegerType::class, [
                'label' => 'Ordre d’affichage',
                'attr' => [
                    'min' => 0,
                ],
                'help' => 'Plus le nombre est petit, plus le produit apparaît tôt dans sa catégorie.',
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
