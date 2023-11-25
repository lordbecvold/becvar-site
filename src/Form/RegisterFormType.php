<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * RegisterFormType provides a form for registering new admin users.
 *
 * @see AbstractType
 */
class RegisterFormType extends AbstractType
{
    /**
     * Builds the registration form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array<string, mixed> $options The options for building the form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'text-input',
                    'autocomplete' => 'username',
                    'placeholder' => 'Username',
                ],
                'mapped' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username',
                    ]),
                    new Length([
                        'min' => 4,
                        'minMessage' => 'Your username should be at least {{ limit }} characters',
                        'max' => 50,
                    ]),
                ],
                'translation_domain' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'text-input',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Password',
                ],
                'mapped' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 80,
                    ]),
                ],
                'translation_domain' => false
            ])
            ->add('re-password', PasswordType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'type' => 'password',
                    'class' => 'text-input',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Password again',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password again',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password again should be at least {{ limit }} characters',
                        'max' => 80,
                    ]),
                ],
                'translation_domain' => false
            ])
        ;
    }

    /**
     * Configures the options for this form.
     *
     * @param OptionsResolver $resolver The resolver for the form options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
