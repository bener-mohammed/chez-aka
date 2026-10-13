<?php

namespace App\Form;

use App\Entity\AppUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])

            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])

            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])

            ->add('phoneNumber', TelType::class, [
                'label' => 'Téléphone',
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'constraints' => [
                    new NotBlank(
                        message: 'Le mot de passe est obligatoire.'
                    ),
                    new Length(
                        min: 12,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        max: 4096
                    ),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J’accepte les conditions d’utilisation et la politique de confidentialité.',
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter les conditions pour créer un compte.'
                    ),
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => AppUser::class,
        ]);
    }
}